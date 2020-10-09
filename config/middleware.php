<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

use Tranquillity\App\Errors\Helpers\ErrorRenderer;

return static function (App $app) {
    // Get logger from container
    $container = $app->getContainer();
    $logger = $container->get(LoggerInterface::class);

    // Register middlewares
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(ContentLengthMiddleware::class);
    $errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);

    // Add custom error renderer
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('application/vnd.api+json', ErrorRenderer::class);
    $errorHandler->forceContentType('application/vnd.api+json');
};