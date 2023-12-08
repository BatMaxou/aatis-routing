# Aatis Routing

## Installation

```bash
composer require aatis/routing
```

## Usage

### Requirements

First, add the router into your container.

```yaml
# config/services.yaml

include_services:
  - 'Aatis\Routing\Service\Router'
```

You can give to this router multiple arguments

```yaml
Aatis\Routing\Service\Router:
    arguments:
        baseHomeController: 'Path\To\Your\HomeController',
        templateRenderer: 'Path\To\Your\TemplateRenderer',
        notFoundErrorTemplate: 'Path\To\Your\NotFoundErrorTemplate',
        notFoundErrorVars:
            template_var1: 404
            template_var2: "Page not found !"
```

*notFoundErrorTemplate (default: /errors/error.tpl.php) and notFoundErrorVars (default: []) are optional*

### Controller

Each controller must extends the abstract class **AbstractController**

```php
class AatisController extends AbstractController
{
    // ...
}
```

### Home Controller

In your application, you may have a home controller which extends the abstract class **AbstractHomeController**

```php
class AatisHomeController extends AbstractHomeController
{
    public function home(): void
    {
        // ...
    }
}
```

*For the home method, **Route** attibutes are not required*

### Routes

You can create your routes in your controller like the following example :

```php
#[Route('/hello')]
public function hello(): void
{
    // ...
}
```

*You can give multiple **Route** to a same controller function*

*You can't give the same **Route** to multiple controller functions*

### Routes with parameters

You can also give parameters to your routes like the following example :

```php
#[Route('/hello/{name}/{age}')]
public function hello(string $name, int $age): void
{
    // ...
}
```

### TODO
