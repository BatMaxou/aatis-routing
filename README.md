# Aatis Routing

## Installation

```bash
composer require aatis/routing
```

## Usage

### Requirements

First, add the router into your container config.

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
        notFoundErrorTemplate: 'path/template.tpl',
        notFoundErrorVars:
            template_var1: 404
            template_var2: "Page not found !"
```

*notFoundErrorTemplate (default: /errors/error.tpl.php) and notFoundErrorVars (default: []) are optional*

### Controller

Each controller must extends the abstract class `AbstractController`.

```php
class AatisController extends AbstractController
{
    // ...
}
```

The `AbstractController` class provide a method `render` thats allows you to render a template.

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

### Home Controller

In your application, you must have a home controller which extends the abstract class `AbstractHomeController`.

```php
class AatisHomeController extends AbstractHomeController
{
    public function home(): void
    {
        // ...
    }
}
```

*For the home method, `Route` attibutes are not required*

### Routes

You can create your routes in your controller like the following example :

```php
#[Route('/hello')]
public function hello(): void
{
    // ...
}
```

*You can give multiple `Route` to a same controller function*

*You can't give the same `Route` to multiple controller functions*

### Routes with parameters

You can also give parameters to your routes like the following example :

```php
#[Route('/hello/{name}/{age}')]
public function hello(string $name, int $age): void
{
    // ...
}
```
