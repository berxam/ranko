# APILLO - Microframework for webapps

Minimal framework for simple API creation with PHP. Launch your apps in the blink of an eye!

## Installation

```
git clone https://github.com/berxam/apillo.git
```

Composer compatibility & [boilerplate project](https://github.com/berxam/apillo-starter) soon to become.

## Usage
```
<?php

require_once "../apillo/src/apillo.php";

$app = new Apillo;

$app->get("/", function ($res) {
    $res->respond([
        "msg" => "Hello world!"
    ]);
});

$app->run();

?>

```

See more at [berxam.com/apillo/docs]().

## Features

Apillo combines a router with a neat set of helper methods for RESTful API development.

- dynamic routing to controller functions
- easier request parameter access with ```params($key = null)```
- methods ```respond()``` and ```show()``` for outputting JSON / HTML
- ability to set environment variables with ```.env``` file
