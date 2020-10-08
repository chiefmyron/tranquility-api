<?php declare(strict_types=1);

use Tranquillity\Utility\ArrayHelper;

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null) {
        $value = ArrayHelper::get($_ENV, $key, false);
        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        // Remove quotes from value
        if (strlen($value) > 1 && (substr($value, 0, 1) == '"') && (substr($value, -1, 1) == '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}