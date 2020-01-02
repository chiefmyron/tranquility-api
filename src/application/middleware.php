<?php

declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

use Tranquility\App\Errors\Helpers\ErrorRenderer;

return static function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    // Add content length middleware last (so that it is the last to be executed on the way out)
    $contentLengthMiddleware = new ContentLengthMiddleware();
    $app->add($contentLengthMiddleware);
    
    // Add error handling
    // TODO: Set flags from config files
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Add custom error renderer
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('application/vnd.api+json', ErrorRenderer::class);
};