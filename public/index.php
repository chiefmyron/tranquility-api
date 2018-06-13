<?php
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));

// Bootstrap the application
require('../vendor/autoload.php');
$app = require('../src/application/bootstrap.php');

// Register routes with application
require('../src/application/routes.php');

// Execute the request
$app->run();