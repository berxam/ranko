<?php

namespace Ranko\Http;

/**
 * Request - ranko
 * ----------------------------------------------
 * Parses request data and gives access to it.
 */
class Request {

    /** @var string Parsed url of request. */
    public $url;

    /** @var array Body of request. */
    public $body;

    /**
     * Sets url and body.
     * 
     * @param string $baseUrl Optional. Project root.
     */
    public function __construct ($baseUrl = "") {
        $this->setUrl($baseUrl);
        $this->setBody();
    }

    /**
     * Get request headers or read a value from them by key.
     *
     * The headers are cached in a static variable to
     * increase performance if called multiple times. 
     *
     * @param  string $key Optional key.
     * @return array|string Request headers with lowercased keys, or a value.
     */
    public function getHeaders ($key = null) {
        static $headers = [];

        if (empty($headers)) {
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        }

        return $key && !empty($headers[$key])
            ? $headers[$key]
            : $headers;
    }

    /**
     * Get request body or read a value from it by key.
     *
     * @param   string $key Optional key.
     * @return  mixed Body as associative array or value from it.
     */
    public function getBody ($key = null) {
        if ($key) {
            if (!empty($this->body[$key])) {
                return $this->body[$key];
            }
        } else {
            return $this->body;
        }

        return null;
    }

    /**
     * Get the mime type in the `Content-Type` header.
     * 
     * @return string|null The mime type, or null if none is available.
     */
    public function getContentType () {
        $contentType = $this->getHeaders('content-type');
        return $contentType
            ? explode(';', $contentType)[0]
            : null;
    }

    /**
     * Gets Accept-Language header and turns it into sorted PHP array.
     * 
     * @return array Holds each language as array with keys "lang" and "q".
     */
    public function acceptsLang ($lang = null) {
        $acceptedLangs = $this->getHeaders('accept-language');
        return self::parseAcceptHeader($acceptedLangs);
    }

    /**
     * Checks if the Accept header contains $type.
     *
     * @param string $type Content-Type, for example "text/html".
     * @return string|false Rest of the header str after type or false.
     */
    public function accepts ($type = null) {
        $accepteds = $this->getHeaders('accept');
        return self::parseAcceptHeader($accepteds);
    }

    private static function parseAcceptHeader ($str) {
        $parts = [];

        foreach (explode(",", $str) as $part) {
            if (strstr($part, ";")) {
                [$part, $q] = explode(";", $part);
                $qPos = strpos($q, "=") + 1;
                $qualityFactor = (float) substr($q, $qPos);
                $parts[$part] = $qualityFactor;
            } else {
                $parts[$part] = 1;
            }
        }

        uasort($parts, function ($a, $b) {
            if ($a == $b) return 0;
            return $a > $b ? -1 : 1;
        });

        return $parts;
    }

    /**
     * Sets request url as $this->url and cleans it up.
     * 
     * If you set the project root in __construct(), it will
     * get deleted from the url here. If request url contains
     * GET params or ending slash, they will also be removed.
     * 
     * @param string $baseUrl Optional. Project root.
     */
    private function setUrl ($baseUrl = NULL) {
        $this->url = $_SERVER["REQUEST_URI"];

        // Remove user specified path
        if ($baseUrl) {
            $this->url = str_replace($baseUrl, "", $this->url);   
        }

        // Remove all GET parameters if there are any
        if (strpos($this->url, "?") !== false) {
            $this->url = strtok($this->url, "?");
        }

        // Remove ending slash if it's not the only char
        if (substr($this->url, -1) == "/" && strlen($this->url) > 1) {
            $this->url = substr_replace($this->url,"", -1);
        }
    }

    /**
     * Calls setters depending on the request method.
     */
    private function setBody () {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                if (!empty($_GET)) $this->body = $_GET;
                break;
            case "POST":
                if (empty($_POST)) {
                    $this->readBody();
                } else {
                    $this->body = $_POST;
                }
                break;
            default:
                $this->readBody();
                break;
        }
    }

    /**
     * Reads body to this->body if it has content.
     */
    private function readBody () {
        $body = file_get_contents('php://input');

        if ($this->contentType() === 'application/json') {
            $body = json_decode($body, true);
        }

        if (!empty($body)) $this->body = $body;
    }
}

?>
