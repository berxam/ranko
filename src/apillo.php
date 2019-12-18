<?php

require_once __DIR__ . "/router.php";
require_once __DIR__ . "/http/request.php";
require_once __DIR__ . "/http/response.php";
require_once __DIR__ . "/dotenv.php";

/**
 * APILLO
 * ----------------------------------------------
 * Minimal framework for quick API creation.
 * 
 * @see apillo/http/Request
 * @see apillo/http/Response
 * @link https://berxam.com/apillo
 */
class Apillo extends Router {

    /**
     * @var Request Request object.
     */
    public $req;

    /**
     * @var Response Response object.
     */
    private $res;

    /**
     * @var callable Function to invoke when no routes match the request.
     */
    private $notFoundProtocol;

    /**
     * Instantiate necessary objects to run Apillo.
     * 
     * @param string $base_URI The subfolder path to remove from request.
     */
    public function __construct ($base_URI = "") {
        $this->req = new Request($base_URI);
        $this->res = new Response;

        // Read the .env file into $_ENV
        Dotenv::init();
    }

    /**
     * Checks req against routes and executes matching route callback or 404.
     */
    public function run () {
        list($controller, $arguments) = $this->checkRoutes();

        if (!$controller) {
            call_user_func(
                $this->notFoundProtocol,
                $this->res,
                $this->req
            );
        } else {
            call_user_func_array($controller, [
                $this->res,
                $this->req,
                $arguments
            ]);
        }
    }

    /**
     * Set the function to invoke when no routes match request.
     * 
     * @param callable $fn Function to invoke.
     */
    public function set404 ($fn) {
        if (!is_callable($fn)) throw new Exception('Controller has to be callable');
        
        $this->notFoundProtocol = $fn;
    }

    /**
     * Redirects by setting HTTP Location header and exitting.
     *
     * @param string $to     Location where we're redirecting.
     * @param int    $status Optional. HTTP status code.
     */
    public function redirect ($to, $status = 302) {
        header("Location: $to", true, $status);
        exit();
    }

    /**
     * Sets HTTP header Access-Control-Allow-Origin.
     *
     * @param string $origin Optional, defaults to *.
     */
    public function cors ($origin = "*") {
        header("Access-Control-Allow-Origin: $origin");
    }

    /**
     * Checks if request matches user defined routes.
     *
     * @return array|false Array with callback of match and possible placeholders.
     */
    private function checkRoutes () {
        $routes = $this->getRoutes();
        $requested_uri = $this->req->getURI();

        // If there's no routes set up for this REQUEST_METHOD
        if (empty($routes[$_SERVER["REQUEST_METHOD"]])) return false;

        // Go through each route of this REQUEST_METHOD
        foreach ($routes[$_SERVER["REQUEST_METHOD"]] as $route) {
            $uri = $route["uri"];
            
            if (strstr($uri, "{")) {
                $placeholders = $this->matchPattern($uri, $requested_uri);

                if (!empty($placeholders)) {
                    return [
                        $route["ctrl"],
                        $placeholders
                    ];
                }
            } else {
                if ($uri === $requested_uri) {
                    return [
                        $route["ctrl"],
                        []
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Extracts URI placeholders from pattern and matches real URI to it.
     * 
     * @return array Placeholders from the URI
     */
    private function matchPattern ($pattern, $uri) {
        // Replace all curly braces matches {}
        $pattern = preg_replace('/\/{(.*?)}/', '/(.*?)', $pattern);
                
        // URI matched the requested URI
        if (preg_match_all('#^' . $pattern . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
            // Rework matches to only contain the matches, not the orig string
            $matches = array_slice($matches, 1);

            // Define the map callback
            $callback = function ($match, $index) use ($matches) {
                // If we have a following parameter
                if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                    // Take substring from the current position until the next parameters position
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                }

                // We have no following parameters
                return isset($match[0][0]) ? trim($match[0][0], '/') : null;
            };

            // Extract the matched URI placeholders
            $placeholders = array_map($callback, $matches, array_keys($matches));

            return $placeholders;
        }
    }

}

?>