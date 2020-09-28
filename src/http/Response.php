<?php

namespace Ranko\Http;

/**
 * Response - ranko
 * ----------------------------------------------
 * Provides functionality to respond.
 */
class Response {

    public function withStatus (int $code) {
        http_response_code($code);
        return $this;
    }

    public static function sendStatus (int $code) {
        http_response_code($code);
    }

    /**
     * Outputs a plain text response.
     *
     * @param string $response Text response.
     */
    public static function sendText (string $response) {
        header("Content-Type: text/plain; charset=utf-8");
        echo $response;
    }

    /**
     * Outputs a JSON response.
     *
     * @param mixed $response A json encodable response.
     */
    public static function sendJSON (/*mixed*/ $response) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($response);
    }

    /**
     * Outputs a HTML response.
     *
     * @param string $response HTML string.
     */
    public static function sendHTML (string $response) {
        header("Content-Type: text/html; charset=utf-8");
        echo $response;
    }

    /**
     * Render a view.
     * 
     * @param string $view     Path of PHP file.
     * @param mixed ...$params Params passed on to the view.
     */
    public static function render (string $view, /*mixed*/...$params) {
        $export = require $view;
        if (!is_callable($export))
            throw new \Exception("A view file must return a function.");

        header("Content-Type: text/html; charset=utf-8");
        return call_user_func($export, ...$params);
    }

    /**
     * Redirects by setting HTTP Location header and exitting.
     *
     * @param string $to     Location where we're redirecting.
     * @param int    $status Optional. HTTP status code.
     */
    public function redirect (string $to, int $status = 302) {
        header("Location: $to", true, $status);
        exit;
    }
}

?>
