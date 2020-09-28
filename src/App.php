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

    /** @var array Global middlewares invoked on every request. */
    private $middleware = [];

    /**
     * Instantiate a Ranko application.
     * 
     * @param string $baseUrl The subfolder path to remove from request.
     */
    public function __construct (string $baseUrl = "") {
        $this->req = new Request($baseUrl);
        $this->res = new Response;

        $this->fourToFive = function ($status, $req, $res) {
            $res->withStatus($status)
                ->sendJSON([ "error" => $status ]);
        };
    }

    public function use (callable ...$middleware) {
        array_push($this->middleware, ...$middleware);
    }

    /**
     * Checks routes and executes matching route controllers or fourToFive.
     */
    public function run () {
        $match = $this->checkRoutes();

        if ($match === false) return $this->callFourToFive(404);

        [$controllers, $placeholders] = $match;
        $this->req->params = $placeholders;
        $availableMethods = array_keys($controllers);
        header("Access-Control-Allow-Methods: ".implode(', ', $availableMethods));

        if (!in_array($_SERVER['REQUEST_METHOD'], $availableMethods)) {
            return $this->callFourToFive(405);
        }

        $methodControllers = $controllers[$_SERVER['REQUEST_METHOD']];

        $stack = array_merge($this->middleware, $methodControllers);
        $next = \Closure::bind(function () use ($stack, &$next) {
            static $i = 0;

            if ($fn = $stack[$i++] ?? null) {
                try {
                    call_user_func($fn, $this->req, $this->res, $next);
                } catch (\Throwable $th) {
                    $this->callFourToFive($th->statusCode ?? 500);
                }
            } else {
                trigger_error("Called `next` from last middleware of stack. This is a no-op.", E_USER_NOTICE);
            }
        }, $this);

        $next();
    }

    private function callFourToFive (int $status) {
        return call_user_func($this->fourToFive, $status, $this->req, $this->res);
    }

    /**
     * Set the function to invoke when there occurs an HTTP error
     * during runtime, for example when no routes match request.
     *
     * @param callable $fn Function to invoke.
     */
    public function setFourToFive (callable $fn) {
        $this->fourToFive = $fn;
    }

    /**
     * Checks if request matches user defined routes.
     *
     * @return array|false Array with route controllers and possible placeholders.
     */
    private function checkRoutes () {
        foreach ($this->routes as $route => $controllers) {
            $match = parent::matchTemplate($route, $this->req->url);

            if ($match !== false) {
                return [ $controllers, $match ];
            }
        }

        return false;
    }
}

?>