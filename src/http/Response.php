<?php

/**
 * Response - APILLO
 * ----------------------------------------------
 * Provides functionality to respond with JSON.
 */
class Response {
    public static $JSON = 'Content-type: application/json; charset=utf-8';
    public static $HTML = 'Content-type: text/html; charset=utf-8';

    public static $STATUS_CODES = [
        200 => '200 OK',
        201 => '201 Created',
        404 => '404 Not Found'
    ];

    /**
     * Exits PHP with a JSON response and proper headers.
     *
     * @param array|string $response PHP array or JSON string.
     * @param int          $status   HTTP status code.
     */
    public function respond ($response, $status = 200) {
        header('HTTP/1.1 ' . self::$STATUS_CODES[$status]);
        header(self::$JSON);

        exit(json_encode($response));
    }

    /**
     * Exits PHP with a HTML response and proper headers.
     *
     * @param array|string $file   PHP or HTML filepath.
     * @param int          $status HTTP status code.
     */
    public function show ($file, $status = 200) {
        header('HTTP/1.1 ' . self::$STATUS_CODES[$status]);
        header(self::$HTML);

        // Check if file exists
        if (file_exists($file)) {
            // File exists, let's get its extension
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            switch ($ext) {
                case 'php':
                    require_once $file;
                    break;
                case 'html':
                    echo file_get_contents($file);
                    break;
                default:
                    $this->respond(["error" => "Incorrect file extension"], 404);
            }
        } else {
            $this->respond(["error" => "File doesn't exist"], 404);
        }
    }
}

?>
