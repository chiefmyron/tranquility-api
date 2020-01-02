<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Tranquility\App\Config;

return static function() {
    // Initialise environment variables
    $dotenv = new Dotenv(TRANQUIL_PATH_BASE);
    $dotenv->load();

    // Load configuration from files
    $config = new Config();
    $config->load(TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'config');
    return $config;
};