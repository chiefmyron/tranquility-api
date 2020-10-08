<?php declare(strict_types=1);

// Library classes
use Symfony\Component\Dotenv\Dotenv;

// Application classes
use Tranquillity\Utility\Config;

return static function() {
    // Initialise environment variables
    $dotenv = new Dotenv();
    $dotenv->load(APP_BASE_PATH.'/.env');

    // Load settings from files
    $config = new Config();
    $config->load(APP_BASE_PATH.'/config/settings');
    return $config;
};