<?php

namespace Ranko;

use Ranko\Http\{ Request, Response };
use Ranko\{ Router };

/**
 * ranko
 * ----------------------------------------------
 * Minimal framework for quick web app creation.
 */
class App extends Router {

    /** @var Request Request object. */
    public $req;

    /** @var Response Response object. */
    private $res;

    /**
     * @var callable Function to invoke when HTTP error occurs.
     */
    private $fourToFive;

    /**
     * Instantiate a Ranko application.
     * 
     * @param string $baseUrl The subfolder path to remove from request.
     */
    public function __construct ($baseUrl = "") {
        $this->req = new Request($baseUrl);
        $this->res = new Response;

        $this->fourToFive = function ($status, $req, $res) {
            $res->withJSON([ "error" => $status ], $status);
        };
    }

    /**
     * Checks routes and executes matching route controllers or fourToFive.
     */
    public function run () {
        $match = $this->checkRoutes();

        if ($match === false) {
            return call_user_func_array($this->fourToFive, [
                404, $this->req, $this->res
            ]);
        }

        [$controllers, $placeholders] = $match;
        $availableMethods = array_keys($controllers);
        header("Access-Control-Allow-Methods: "
            .implode(', ', $availableMethods));

        if (!in_array($_SERVER['REQUEST_METHOD'], $availableMethods)) {
            return call_user_func_array($this->fourToFive, [
                405, $this->req, $this->res
            ]);
        }

        $methodControllers = $controllers[$_SERVER['REQUEST_METHOD']];

        try {
            foreach ($methodControllers as $controller) {
                call_user_func_array($controller, [
                    $this->req, $this->res, $placeholders
                ]);
            }
        } catch (Throwable $th) {
            call_user_func_array($this->fourToFive, [
                500, $this->req, $this->res
            ]);
        }
    }

    /**
     * Set the function to invoke when there occurs an HTTP error
     * during runtime, for example when no routes match request.
     *
     * @param callable $fn Function to invoke.
     */
    public function setFourToFive ($fn) {
        if (!is_callable($fn)) throw new \InvalidArgumentException('Controller has to be callable');
        
        $this->fourToFive = $fn;
    }

    /**
     * Checks if request matches user defined routes.
     *
     * @return array|false Array with route controllers and possible placeholders.
     */
    private function checkRoutes () {
        foreach ($this->routes as $route => $controllers) {
            $match = self::matchTemplate($route, $this->req->url);

            if ($match !== false) {
                return [ $controllers, $match ];
            }
        }

        return false;
    }
}

?>