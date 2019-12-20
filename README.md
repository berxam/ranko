# ranko • micro framework for webapps

Minimal framework for simple API or web app creation with PHP.

## Installation

Test the unstable version of this via [Composer](https://getcomposer.org)
```
composer require berxam/ranko:dev-master
```

## Usage
```
<?php

require_once "./vendor/autoload.php";

$app = new Ranko\Ranko;

$app->get("/", function ($res) {
    $res->respond(["msg" => "Hello world!"]);
});

$app->run();

?>
```

Documentation soon to come.

## Features

Ranko combines a router with a neat set of helper methods for web app development.

- dynamic routing to controller functions
- easier request parameter access with ```params($key = null)```
- methods ```respond()``` and ```show()``` for outputting JSON / HTML
- ability to set environment variables with ```.env``` file
