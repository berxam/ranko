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
    public function __construct (?string $baseUrl = "") {
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
    public function getHeaders (?string $key = null) {
        static $headers = [];

        if (empty($headers)) {
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        }

        if ($key) return $headers[$key] ?? null;

        return $headers;
    }

    /**
     * Get request body or read a value from it by key.
     *
     * @param   string $key Optional key.
     * @return  mixed Body as associative array or value from it.
     */
    public function getBody (?string $key = null) {
        if ($key) {
            return $this->body[$key] ?? null;
        } else {
            return $this->body;
        }
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
     * @param string $lang Language code, for example "en-US".
     * @return boolean
     */
    public function acceptsLang (string $lang): bool {
        static $acceptedLangs = null;

        if ($acceptedLangs === null) {
            $acceptedLangs = self::parseAcceptHeader(
                $this->getHeaders('accept-language')
            );
        }

        return key_exists($lang, $acceptedLangs);
    }

    /**
     * Checks if the Accept header contains $type.
     *
     * @param string $type Content-Type, for example "text/html".
     * @return boolean
     */
    public function accepts (string $type): bool {
        static $accept = null;

        if ($accept === null) {
            $accept = self::parseAcceptHeader($this->getHeaders('accept'));
        }

        return key_exists($type, $accept);
    }

    private static function parseAcceptHeader (string $str): array {
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
    private function setUrl (?string $baseUrl = "") {
        $this->url = $_SERVER["REQUEST_URI"];

        // Remove all GET parameters
        $this->url = strtok($this->url, "?");

        // Remove user specified path
        if (!empty($baseUrl)) {
            $baseUrlPos = strpos($this->url, $baseUrl);
            if ($baseUrlPos === 0) {
                $this->url = substr_replace($this->url, "", $baseUrlPos, strlen($baseUrl));
            }
        }

        // Remove name of called script (e.g. `index.php`) if starts with it
        $parts = explode("/", $this->url);

        if (strtolower($parts[1]) === strtolower(basename($_SERVER["SCRIPT_FILENAME"]))) {
            $parts[1] = "";
            if (count($parts) > 2) unset($parts[0]);
        }

        // Remove trailing slash
        if (end($parts) === "" && count($parts) > 2) array_pop($parts);

        $this->url = implode("/", $parts);
    }

    /**
     * Calls setters depending on the request method.
     */
    private function setBody () {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "HEAD":
            case "GET":
                break;
            default:
                if (!empty($_POST)) {
                    $this->body = $_POST;
                    break;
                }
        
                $body = file_get_contents('php://input');
        
                if ($this->getContentType() === 'application/json') {
                    $body = json_decode($body, true);
                }
        
                if (!empty($body)) $this->body = $body;
                break;
        }
    }
}

?>
