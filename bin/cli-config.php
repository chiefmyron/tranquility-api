<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Slim\Container;

// Load application container
$container = require_once( __DIR__.'/cli-bootstrap.php' );

// Register entity manager with the console runner
ConsoleRunner::run(ConsoleRunner::createHelperSet($container[EntityManager::class]));