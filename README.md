# ranko • micro framework for webapps

Minimal framework for simple API or web app creation with PHP.

## Installation

I recommend to get started with the [boilerplate project](https://github.com/berxam/ranko-starter), but you can also install via [Composer]().
```
composer require berxam/ranko
```

## Usage
```
<?php

require_once "./vendor/autoload.php";

$app = new ranko\Ranko;

$app->get("/", function ($res) {
    $res->respond(["msg" => "Hello world!"]);
});

$app->run();

?>
```

See more at [berxam.com/ranko/docs]().

## Features

Ranko combines a router with a neat set of helper methods for web app development.

- dynamic routing to controller functions
- easier request parameter access with ```params($key = null)```
- methods ```respond()``` and ```show()``` for outputting JSON / HTML
- ability to set environment variables with ```.env``` file
