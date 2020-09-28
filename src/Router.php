<?php

namespace Ranko;

/**
 * Router - ranko
 * ----------------------------------------------
 * Holds an array of routes and their callbacks,
 * ability to work with the array.
 */
class Router {

    /**  @var array All the routes and their callbacks. */
    public $routes = [];

    /**
     * Adds route accessible by GET HTTP method to routes.
     *
     * @param string    $route URL pattern.
     * @param callable  ...$controllers Controller function.
     */
    public function get (string $route, callable ...$controllers) {
        $this->addRoute("GET", $route, ...$controllers);
    }
    
    /**
     * Adds route accessible by POST HTTP method to routes.
     *
     * @param string    $route URL pattern.
     * @param callable  ...$controllers Controller function.
     */
    public function post (string $route, callable ...$controllers) {
        $this->addRoute("POST", $route, ...$controllers);
    }

    /**
     * Adds route accessible by PUT HTTP method to routes.
     *
     * @param string    $route URL pattern.
     * @param callable  ...$controllers Controller function.
     */
    public function put (string $route, callable ...$controllers) {
        $this->addRoute("PUT", $route, ...$controllers);
    }
    
    /**
     * Adds route accessible by DELETE HTTP method to routes.
     *
     * @param string    $route URL pattern.
     * @param callable  ...$controllers Controller function.
     */
    public function delete (string $route, callable ...$controllers) {
        $this->addRoute("DELETE", $route, ...$controllers);
    }

    /**
     * Adds route accessible by any HTTP method to routes.
     *
     * @param string    $route URL pattern.
     * @param callable  ...$controllers Controller function.
     */
    public function any (string $route, callable ...$controllers) {
        $this->addRoute(
            "GET|HEAD|POST|PUT|DELETE|CONNECT|OPTIONS|TRACE|PATCH",
            $route, ...$controllers
        );
    }

    /**
     * @param string $baseUrl
     * @param Router $router
     */
    public function addRouter (string $baseUrl, Router $router) {
        if ($baseUrl === '/') $baseUrl = '';
        foreach ($router->routes as $url => $routes) {
            $this->routes[$baseUrl.$url] = $routes;
        }
    }

    /**
     * Parses the route and adds it to $this->routes.
     *
     * @param string   $methods Expected HTTP methods, separated with |.
     * @param string   $route   Expected URL path.
     * @param callable ...$controllers   Controller to execute.
     */
    public function addRoute (string $methods, string $route, callable ...$controllers) {
        if (!isset($this->routes[$route])) {
            $this->routes[$route] = [];
        }

        foreach  (explode('|', $methods) as $method) {
            if (!isset($this->routes[$route][$method])) {
                $this->routes[$route][$method] = [];
            }

            foreach ($controllers as $controller) {
                $this->routes[$route][$method][] = $controller;
            }
        }
    }

    /**
     * Checks if a URL matches a URL template & extracts placeholders if any.
     *
     * @param string  $template      URL template like "/users/:id" (colon to mark placeholders)
     * @param string  $url           Real URL to compare the template to
     * @param boolean $caseSensitive Optional. Defaults to false.
     * @return boolean|array
     *      If there are placeholders in the template, they will be returned
     *      as an associative array like [ "id" => "42" ], otherwise return
     *      value will be boolean based on success to match.
     */
    public static function matchTemplate (
            string $template, string $url, bool $caseSensitive = false)/*: mixed */{
        $templatePieces = explode("/", $template);
        $urlPieces = explode("/", $url);

        if (count($templatePieces) !== count($urlPieces)) return false;

        $placeholders = [];

        for ($i = 0; $i < count($templatePieces); $i++) {
            $templatePart = $templatePieces[$i];
            $urlPart = $urlPieces[$i];

            // If there is a colon in template, get following string ($match[1])
            if (preg_match('/:(.*)/', $templatePart, $match)) {
                if (empty($urlPart)) return false;
                $placeholders[ $match[1] ] = $urlPart;
            }
            // No colon, so just compare the two strings
            else {
                if ($caseSensitive == false) {
                    $templatePart = strtolower($templatePart);
                    $urlPart = strtolower($urlPart);
                }
                if ($templatePart !== $urlPart) return false;
            }
        }
        // If we reached this point, URL matches template
        return empty($placeholders) ? true : $placeholders;
    }
}

?>
