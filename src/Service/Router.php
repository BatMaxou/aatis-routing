<?php

namespace Aatis\Routing\Service;

use Aatis\DependencyInjection\Component\Service;
use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\DependencyInjection\Service\ServiceInstanciator;
use Aatis\HttpFoundation\Component\Request;
use Aatis\HttpFoundation\Component\Response;
use Aatis\Routing\Attribute\Route;
use Aatis\Routing\Controller\AatisController;
use Aatis\Routing\Exception\InvalidArgumentException;
use Aatis\Routing\Exception\NotAllowedMethodException;
use Aatis\Routing\Exception\NotValidRouteException;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;
use Psr\Log\LoggerInterface;

class Router
{
    /**
     * @var Route[]
     */
    private array $routes = [];

    /**
     * @param array<string, mixed> $notFoundErrorVars
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly AatisController $baseController,
        private readonly TemplateRendererInterface $templateRenderer,
        private readonly RequestStack $requestStack,
        private readonly string $notFoundErrorTemplate = '/errors/error.tpl.php',
        private readonly array $notFoundErrorVars = [],
        private readonly ?LoggerInterface $logger = null,
    ) {
        /** @var Service[] */
        $controllerServices = $this->container->getByTag('controller', true);
        foreach ($controllerServices as $controllerService) {
            $this->extractRoutes($controllerService->getClass());
        }
    }

    public function redirect(Request $request): Response
    {
        $requestUri = $request->server->get('REQUEST_URI');
        if (!is_string($requestUri)) {
            throw new NotValidRouteException('The request URI is not a string');
        }

        $httpMethod = $request->server->get('REQUEST_METHOD');
        if (!is_string($httpMethod)) {
            throw new \LogicException('The request method is not defined');
        }

        $this->requestStack->push($request);

        $explodedUri = $this->explodeUri($requestUri);
        $routeInfos = $this->findRoute($explodedUri);

        if ($routeInfos) {
            $route = $routeInfos['route'];

            if (!empty($route->gethttpMethodsAllowed()) && !in_array($httpMethod, $route->gethttpMethodsAllowed())) {
                throw new NotAllowedMethodException(sprintf('The method %s is not allowed for the route %s', $httpMethod, $route->getPath()));
            }

            $params = $routeInfos['params'];

            $namespace = $route->getController();
            if (!$namespace) {
                throw new NotValidRouteException(sprintf('The route %s isn\'t linked to a controller', $route->getPath()));
            }

            $controller = $this->container->get($namespace);

            $params = [];
            $loggedParams = [];
            foreach ($route->getMethodParams() as $key => $type) {
                if (isset($routeInfos['params'][$key])) {
                    $value = $routeInfos['params'][$key];

                    $params[] = $value;
                    if ($this->logger) {
                        $loggedParams[$key] = $value;
                    }

                    continue;
                }

                if (Request::class === $type) {
                    $params[] = $request;

                    continue;
                }

                if (str_starts_with($key, '_')) {
                    $params[] = $this->container->get(sprintf('APP%s', strtoupper($key)));

                    continue;
                }

                if (interface_exists($type)) {
                    /** @var Service[] */
                    $services = $this->container->getByInterface($type, true);
                    $params[] = $this->chooseService($services);

                    continue;
                }

                try {
                    $params[] = $this->container->get($type);
                } catch (\Exception) {
                    throw new InvalidArgumentException(sprintf('The parameter %s of the function %s of %s controller is not provided or can\'t be autowired', $key, $route->getMethodName(), $route->getController()));
                }
            }

            $response = $controller->{$route->getMethodName()}(...$params);

            $this->logger?->info(sprintf(
                '%s %s %s %s',
                $response->getStatusCode(),
                $httpMethod,
                $requestUri,
                empty($loggedParams) ? '' : (json_encode($loggedParams) ?: '{}'),
            ));

            return $response;
        }

        if (isset($explodedUri[1]) && '' === $explodedUri[1] && 'GET' === $httpMethod) {
            $response = $this->baseController->home();

            $this->logger?->info(sprintf('%s %s /', $response->getStatusCode(), $httpMethod));

            return $response;
        }

        $this->logger?->error(sprintf('404 %s %s', $httpMethod, $requestUri));

        try {
            return new Response($this->templateRenderer->render($this->notFoundErrorTemplate, $this->notFoundErrorVars), 404);
        } catch (\Exception) {
            return new Response($this->templateRenderer->render('notFound.tpl.php', ['overrideLocation' => '../vendor/aatis/routing/templates']), 404);
        }
    }

    /**
     * @param class-string $controller
     */
    private function extractRoutes(string $controller): void
    {
        $reflection = new \ReflectionClass($controller);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);
            if (empty($attributes)) {
                continue;
            }

            $params = $method->getParameters();
            if (!empty($params)) {
                $params = array_reduce($params, $this->addRouteParameter(...), []);
            }

            foreach ($attributes as $attribute) {
                $args = $attribute->getArguments();

                if (empty($args)) {
                    throw new NotValidRouteException(sprintf('The function %s of %s controller isn\'t linked to a route', $method->getName(), $controller));
                }

                foreach ($this->routes as $route) {
                    if ($route->getPath() === $args[0]) {
                        throw new NotValidRouteException(sprintf('The path %s is already used for function %s of %s controller', $args[0], $route->getMethodName(), $route->getController()));
                    }
                }

                $this->routes[] = (new Route(...$args))
                    ->setController($controller)
                    ->setMethodName($method->getName())
                    ->setMethodParams($params);
            }
        }
    }

    /**
     * @return string[]
     */
    private function explodeUri(string $uri): array
    {
        return explode('/', explode('?', $uri)[0] ?? '');
    }

    /**
     * @param string[] $explodedUri
     *
     * @return array{
     *  route: Route,
     *  params: array<string, string|int>
     * }|null
     */
    private function findRoute(array $explodedUri): ?array
    {
        $foundedRoute = null;
        $params = [];

        foreach ($this->routes as $route) {
            $explodedPath = $this->explodeUri($route->getPath());

            if (count($explodedPath) !== count($explodedUri)) {
                continue;
            }

            for ($i = 0; $i < count($explodedPath); ++$i) {
                $key = substr($explodedPath[$i], 1, -1);
                if (
                    preg_match('/^{.*}$/', $explodedPath[$i])
                    && isset($route->getMethodParams()[$key])
                ) {
                    if ('int' === $route->getMethodParams()[$key]) {
                        if (!is_numeric($explodedUri[$i])) {
                            continue 2;
                        }

                        $explodedUri[$i] = (int) $explodedUri[$i];
                    }
                    $params[$key] = $explodedUri[$i];
                    continue;
                }

                if ($explodedPath[$i] !== $explodedUri[$i]) {
                    continue 2;
                }
            }

            $foundedRoute = $route;
        }

        return $foundedRoute ? [
            'route' => $foundedRoute,
            'params' => $params,
        ] : null;
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return array<string, string>
     */
    private function addRouteParameter(array $parameters, \ReflectionParameter $parameter): array
    {
        /**
         * @var \ReflectionNamedType|null $type
         */
        $type = $parameter->getType();
        if ($type) {
            $parameters[$parameter->getName()] = $type->getName();
        }

        return $parameters;
    }

    /**
     * @param Service[] $services
     */
    private function chooseService(array $services): object
    {
        $i = 0;
        $choosenService = null;

        while ($i < count($services) && !$choosenService) {
            if ($instance = $services[$i]->getInstance()) {
                $choosenService = $instance;
            }
            ++$i;
        }

        if ($choosenService) {
            return $choosenService;
        }

        /** @var ServiceInstanciator */
        $serviceInstanciator = $this->container->get(ServiceInstanciator::class);

        return $serviceInstanciator->instanciate($services[0]);
    }
}
