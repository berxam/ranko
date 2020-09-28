# ranko • micro framework for web apps

Minimal framework for creating RESTful APIs or simple web apps with PHP.

## Installation

via [Composer](https://getcomposer.org)
```
composer require berxam/ranko
```

## Features

Ranko is basically just a router to which you can mount routes with their corresponding callables, a `Request` and `Response` which get passed to each forementioned callable.
- bind controller functions to HTTP methods and URLs or URL templates like `/users/:id`
- access request body and headers easily through `Request`
- respond to client with `Response` methods like `sendJSON(mixed $response)` and `render(string $view, mixed ...$params)`

## Usage

The best way to understand how this "framework" works is to just skim through the files in this repo. This whole project is less than 500 lines.

### Hello world

`index.php`:
```php
<?php
    require_once './vendor/autoload.php';

    $app = new Ranko\App;

    $app->get('/hello', function ($req, $res) {
        $res->sendJSON(['msg' => 'Hello world!']);
    });

    $app->get('/hello/:world', function ($req, $res) {
        $world = $req->params['world'];
        $res->sendHTML("<h1>Hello, $world!</h1>");
    });

    $app->run();
?>
```

Note that you have to direct all requests to `index.php`. If you're running PHP on an Apache server, you can use this ```.htaccess``` rewrite:
```apacheconf
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```
