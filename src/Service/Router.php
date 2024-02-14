<?php

namespace Aatis\Routing\Service;

use Aatis\Routing\Entity\Route;
use Aatis\DependencyInjection\Entity\Service;
use Aatis\Routing\Exception\NotValidRouteException;
use Aatis\Routing\Interface\HomeControllerInterface;
use Aatis\DependencyInjection\Interface\ContainerInterface;
use Aatis\TemplateRenderer\Interface\TemplateRendererInterface;

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
        private readonly HomeControllerInterface $baseHomeController,
        private readonly TemplateRendererInterface $templateRenderer,
        private readonly string $notFoundErrorTemplate = '/errors/error.tpl.php',
        private readonly array $notFoundErrorVars = []
    ) {
        /** @var Service[] */
        $controllerServices = $this->container->getByTag('controller', true);
        foreach ($controllerServices as $controllerService) {
            $this->extractRoutes($controllerService->getClass());
        }
    }

    public function redirect(): void
    {
        $explodedUri = $this->explodeUri($_SERVER['REQUEST_URI']);
        $routeInfos = $this->findRoute($explodedUri);

        if ($routeInfos) {
            $route = $routeInfos['route'];
            $params = $routeInfos['params'];

            $namespace = $route->getController();
            if (!$namespace) {
                throw new NotValidRouteException(sprintf('The route %s isn\'t linked to a controller', $route->getPath()));
            }

            $controller = $this->container->get($namespace);
            $controller->{$route->getMethodName()}(...$params);

            return;
        }

        if (empty($this->routes)) {
            $this->baseHomeController->home();

            return;
        }

        if (isset($explodedUri[1]) && '' === $explodedUri[1]) {
            $path = array_reduce(
                $this->routes,
                fn ($carry, $route) => $this->baseHomeController::class === $route->getController()
                    && 'home' === $route->getMethodName() ? $route->getPath() : $carry,
                null
            );

            if ($path) {
                header('Location: '.$path);
                exit;
            }
        }

        header('HTTP/1.0 404 Not Found');
        $this->templateRenderer->render($this->notFoundErrorTemplate, $this->notFoundErrorVars);
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
                $params = array_reduce($params, function ($carry, $param) {
                    /**
                     * @var \ReflectionNamedType|null $type
                     */
                    $type = $param->getType();
                    if ($type) {
                        $carry[$param->getName()] = $type->getName();
                    }

                    return $carry;
                }, []);
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
}
