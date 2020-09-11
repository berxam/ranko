<?php

namespace Ranko\Http;

/**
 * Response - ranko
 * ----------------------------------------------
 * Provides functionality to respond.
 */
class Response {

    /**
     * Exits PHP with a plain text response.
     *
     * @param string $response Text response.
     * @param int    $status   Optional HTTP status code.
     */
    public static function withText ($response, $status = 200) {
        http_response_code($status);
        header("Content-Type: text/plain; charset=utf-8");
        echo $response;
        exit;
    }

    /**
     * Exits PHP with a JSON response.
     *
     * @param mixed $response A json encodable response.
     * @param int   $status   Optional HTTP status code.
     */
    public static function withJSON ($response, $status = 200) {
        http_response_code($status);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($response);
        exit;
    }

    /**
     * Exits PHP with a HTML response.
     *
     * @param string $response HTML string.
     * @param int    $status   Optional HTTP status code.
     */
    public static function withHTML ($response, $status = 200) {
        http_response_code($status);
        header("Content-Type: text/html; charset=utf-8");
        echo $response;
        exit;
    }

    /**
     * Reads file and responds with it and proper headers.
     *
     * If it's a PHP file it will be required, otherwise
     * it's echoed. Its Content-Type header is guessed
     * based on the extension, but can be overridden.
     * 
     * @param string $file        PHP or HTML filepath.
     * @param int    $status      Optional HTTP status code.
     * @param string $contentType Optional HTTP Content-Type.
     */
    public static function withFile ($file, $status = 200, $contentType = null) {
        $mime = is_null($contentType) ? mime_content_type($file) : $contentType;

        http_response_code($status);
        header("Content-Type: $mime; charset=utf-8");

        if (!file_exists($file)) {
            throw new \Exception("Called `res::withFile` with nonexistent file.");
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if ($ext === 'php') {
            require $file;
        } else {
            echo file_get_contents($file);
        }

        exit;
    }

    /**
     * Redirects by setting HTTP Location header and exitting.
     *
     * @param string $to     Location where we're redirecting.
     * @param int    $status Optional. HTTP status code.
     */
    public function redirect ($to, $status = 302) {
        header("Location: $to", true, $status);
        exit;
    }
}

?>
