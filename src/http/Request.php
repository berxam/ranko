<?php

namespace Ranko\Http;

/**
 * Request - ranko
 * ----------------------------------------------
 * Parses request data and gives access to it
 * via methods getURI() and params().
 */
class Request {

    /**
     * @var string Parsed URI of request.
     */
    private $URI;

    /**
     * @var array Parameters or body of request.
     */
    private $params;

    /**
     * @var array Headers of the request.
     */
    private $headers;

    /**
     * Sets URI and parameters.
     * 
     * @param string $base_url Optional. Project root.
     */
    public function __construct ($base_URI = "") {
        $this->setURI($base_URI);
        $this->setParams();
    }

    /**
     * Gets the parsed version of the request URI.
     * 
     * @return string Parsed request URI.
     */
    public function getURI () {
        return $this->URI;
    }

    /**
     * Gets all request parameters or one by key.
     *
     * If there are no GET or POST params, check if
     * the request has a JSON body, and if so, turn
     * it into a PHP associative array.
     *
     * @param   string|int  $key    Optional. Name of parameter key.
     * @return  mixed|false         Array w/ all params or one match.
     */
    public function params ($key = NULL) {
        if ($key) {
            if (!empty($this->params[$key])) {
                return $this->params[$key];
            }
        } else {
            if (!empty($this->params)) {
                return $this->params;
            }
        }

        return false;
    }

    /**
     * @return boolean Whether Contenty-Type: application/json is set.
     */
    public function contentTypeIsJson () {
        $headers = getallheaders();

        if (empty($headers['Content-Type'])) {
            return false;
        }
        // We have a Content-Type so let's see if its JSON
        else {
            $contentType = $headers['Content-Type'];
            $typeRegEx = "/^application\/json/";
            return preg_match($typeRegEx, $contentType);
        }
    }

    /**
     * Checks if the Accept header contains $type.
     *
     * @param string $type Content-Type, for example "text/html".
     * @return string|false Rest of the header str after type or false.
     */
    public function accepts ($type) {
        return strstr($_SERVER['HTTP_ACCEPT'], $type);
    }

    /**
     * Sets request URI as $this->uri and cleans it up.
     * 
     * If you set the project root in __construct(), it will
     * get deleted from the URI here. If request URI contains
     * GET params or ending slash, they will also be removed.
     * 
     * @param string $base_url Optional. Project root.
     */
    private function setURI ($base_URI = NULL) {
        $this->URI = $_SERVER["REQUEST_URI"];

        // Remove user specified path
        if ($base_URI) {
            $this->URI = str_replace($base_URI, "", $this->URI);   
        }

        // Remove all GET parameters if there are any
        if (strpos($this->URI, "?") !== false) {
            $this->URI = strtok($this->URI, "?");
        }

        // Remove ending slash if it's not the only char
        if (substr($this->URI, -1) == "/" && strlen($this->URI) > 1) {
            $this->URI = substr_replace($this->URI,"", -1);
        }
    }

    /**
     * Calls setters depending on the request method.
     */
    private function setParams () {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                if (!empty($_GET)) $this->params = $_GET;
                break;
            case "POST":
                if (empty($_POST)) {
                    $this->set_json_params();
                } else {
                    $this->params = $_POST;
                }
                break;
            default:
                $this->set_json_params();
                break;
        }
    }

    /**
     * Sets body as $params if it's JSON and has content.
     */
    private function set_json_params () {
        if ($this->contentTypeIsJson()) {
            $body = file_get_contents('php://input');
            $params = json_decode($body, true);

            if (!empty($params)) $this->params = $params;
        }
    }
}

?>
