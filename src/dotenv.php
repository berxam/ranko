<?php

/**
 * Dotenv - APILLO
 * ----------------------------------------------
 * Very simple way to read .env file and add to
 * the $_ENV and getenv() superglobals.
 */
class Dotenv {
    public static function init () {
        // Get the filepath to .env
        $path = getcwd() . "/.env";

        // Open the .env file
        $handle = fopen($path, "r");

        if ($handle) {
            // For each line in the file
            while (($line = fgets($handle)) !== false) {
                // If line doesn't start with # (is not a comment)
                if (strpos($line, "#") !== 0) {
                    // Put line straight to env
                    putenv($line);
                }
            }

            // Close the handle
            fclose($handle);
        } else {
            // Something went wrong
            echo "Failed to load .env";
        }
    }
}

?>
