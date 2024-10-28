- faire jolie page d'accueil
- faire jolie page 404 en accord avec la page d'accueil

- mettre a jour Kernel

```php
public function handle(): void
{
    $request = Request::createFromGlobals();
    $documentRoot = $request->server->get('DOCUMENT_ROOT');

    $dotenv = Dotenv::createImmutable(sprintf('%s../../', $documentRoot), ['.env', '.env.local'], false);
    $dotenv->load();

    $ctx = array_merge(
        array_diff_key($_SERVER, $request->server->all()),
        ['APP_DOCUMENT_ROOT' => $documentRoot ?? '']
    );

    $container = (new ContainerBuilder($ctx))->build();

    /** @var Logger $logger */
    $logger = $container->get(Logger::class);

    /** @var ErrorCodeBag $errorCodeBag */
    $errorCodeBag = $container->get(ErrorCodeBag::class);

    /** @var ExceptionCodeBag */
    $exceptionCodeBag = $container->get(ExceptionCodeBag::class);

    ErrorHandler::initialize($logger, $errorCodeBag, $exceptionCodeBag);

    /** @var Router $router */
    $router = $container->get(Router::class);

    $router->redirect($request)
        ->prepare($request)
        ->send()
    ;
}
```
