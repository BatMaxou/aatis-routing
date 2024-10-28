# Aatis Routing

## Advertisement

This package is a part of `Aatis` and can't be used without the following packages :

- `aatis/dependency-injection` (https://github.com/BatMaxou/aatis-dependency-injection)
- `aatis/template-renderer` (https://github.com/BatMaxou/aatis-template-renderer)
- `aatis/http-foundation` (https://github.com/BatMaxou/aatis-http-foundation)

## Installation

```bash
composer require aatis/routing
```

## Usage

### Requirements

Add the `Router` service into the `Container`.

```yaml
# In config/services.yaml file :

include_services:
  - 'Aatis\Routing\Service\Router'
```

You can give to this router arguments linked to your not found error page.

```yaml
Aatis\Routing\Service\Router:
    arguments:
        notFoundErrorTemplate: 'path/to/your/template',
        notFoundErrorVars:
            template_var1: 404
            template_var2: "Page not found !"
```

> [!NOTE]
>
> - notFoundErrorTemplate (default: /errors/error.tpl.php) is the path to your custom template for the 404 error page.
> - notFoundErrorVars (default: []) is an array of variables that you can use in your custom template. These variables will be pass to the vars parameter of the `TemplateRender`.
> 
> These arguments are optional

### Basic usage

The `Router` will handle the request and provide a response that corresponds to the route, thanks to severals controllers.

```php
$response = $router->redirect($request);
```

> [!WARNING]
> By default, the `Response` provided are not prepared. You have to call the `prepare` method before sending the response.

### Controller

A controller is a class that contains all your routes. Each must extends the abstract class `AbstractController`.

```php
class AatisController extends AbstractController
{
    // ...
}
```

The `AbstractController` class provide a method `render` that allows you to render a template.

```php
class AatisController extends AbstractController
{
    public function hello(): void
    {
        $this->render('template/path', [
            'template_var1' => 'Hello',
            'template_var2' => 'World !'
        ]);
    }
}
```

Into each controller, you have access to the `Container` :

```php
class AatisController extends AbstractController
{
    public function hello(): void
    {
        $service = $this->container->get(Service::class);
    }
}
```

> [!CAUTION]
> Despite its availability, this method is **not recommended**. Use autowiring instead (**see below**)

### Basic Routes

You can create routes into controllers like the following :

```php
#[Route('/hello')]
public function hello(): void
{
    // ...
}
```

> [!NOTE]
> You can give multiple `Route` to a same controller function

> [!CAUTION]
> You **can't** give the same `Route` to multiple controller functions

### Routes with parameters

You can give parameters to your routes :

```php
#[Route('/hello/{name}/{age}')]
public function hello(string $name, int $age): void
{
    // ...
}
```

You can also have access to the `Request` object :

```php
#[Route('/hello/{name}/{age}')]
public function hello(Request $request, string $name, int $age): void
{
    // ...
}
```

Finally, you can also autowire services / interfaces / env variables like into the constructor of a service thanks to the `DependencyInjection` package :

```php
#[Route('/hello/{name}/{age}')]
public function hello(Service $service, string $name, int $age, ServiceInterface $serviceImplementingInterface): void
{
    // ...
}
```

### Route with method

You can also restrict the method of your route :

```php
#[Route('/restricted', method: ['POST', 'DELETE'])]
public function restricted(): void
{
    // ...
}
```

> [!NOTE]
> The method parameter is optional and set to empty by default (all methods are allowed)

### RequestStack

To store the current `Request`, it is possible to put it into the `RequestStack` service.

```php
$request = Request::createFromGlobals();
$requestStack = new RequestStack();

$requestStack->push($request);
```

With the `RequestStack`, the current `Request` is accessible from any service :

```php
class MyService
{
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }
}
```

> [!CAUTION]
> The use of the `RequestStack` is **not recommended**.
