<?php

namespace Ranko;

/**
 * Router - ranko
 * ----------------------------------------------
 * Holds an array of routes and their callbacks,
 * ability to work with the array.
 */
class Router {

    /**
     * @var array All the routes and their callbacks.
     */
    private $routes = [];

    /**
     * @return array All the routes and their callbacks.
     */
    public function getRoutes () {
        return $this->routes;
    }
    
    /**
     * Adds route accessible by GET HTTP method to routes.
     *
     * @param string    $route URI pattern.
     * @param callable  $ctrl Controller function.
     */
    public function get ($route, $ctrl) {
        $this->addRoute("GET", $route, $ctrl);
    }
    
    /**
     * Adds route accessible by POST HTTP method to routes.
     *
     * @param string    $route URI pattern.
     * @param callable  $ctrl Controller function.
     */
    public function post ($route, $ctrl) {
        $this->addRoute("POST", $route, $ctrl);
    }

    /**
     * Adds route accessible by PUT HTTP method to routes.
     *
     * @param string    $route URI pattern.
     * @param callable  $ctrl Controller function.
     */
    public function put ($route, $ctrl) {
        $this->addRoute("PUT", $route, $ctrl);
    }
    
    /**
     * Adds route accessible by DELETE HTTP method to routes.
     *
     * @param string    $route URI pattern.
     * @param callable  $ctrl Controller function.
     */
    public function delete ($route, $ctrl) {
        $this->addRoute("DELETE", $route, $ctrl);
    }

    /**
     * Adds route accessible by "any" HTTP method to routes.
     *
     * @param string    $route URI pattern.
     * @param callable  $ctrl Controller function.
     */
    public function any ($route, $ctrl) {
        $this->addRoute("GET|POST|PUT|DELETE", $route, $ctrl);
    }
    
    /**
     * Parses the route and adds it to $this->routes.
     *
     * @param string   $method Expected HTTP method(s).
     * @param string   $route  Expected URI path.
     * @param callable $ctrl   Controller to execute.
     */
    private function addRoute ($method, $route, $ctrl) {
        if (!is_callable($ctrl)) throw new Exception('Controller has to be callable');

        foreach (explode('|', $method) as $httpMethod) {
            $this->routes[$httpMethod][] = [
                'uri' => $route,
                'ctrl' => $ctrl,
            ];
        }
    }
}

?>
