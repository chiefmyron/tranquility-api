<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Tranquility route-specific middlewares
use Tranquility\Middlewares\AuthenticationMiddleware;
use Tranquility\Middlewares\JsonContentTypeMiddleware;
use Tranquility\Middlewares\JsonApiDocumentFormatMiddleware;

// Tranquility controllers
use Tranquility\Controllers\AuthController as AuthController;
use Tranquility\Controllers\UserController as UserController;
use Tranquility\Controllers\PersonController as PersonController;
use Tranquility\Controllers\AccountController as AccountController;

// Version 1 API routes (unauthenticated)
$app->post('/v1/auth/token', AuthController::class.':token');

// Version 1 API route group (authenticated) middleware
$routeGroupMiddlewares = [
    AuthenticationMiddleware::class,
    JsonApiDocumentFormatMiddleware::class,
    JsonContentTypeMiddleware::class
];

// Version 1 API route group (authenticated)
$routeGroup = $app->group('/v1', function() {
    // User resource
    $this->get('/users', UserController::class.':list')->setName('users-list');
    $this->post('/users', UserController::class.':create');
    $this->get('/users/{id}', UserController::class.':show');
    $this->patch('/users/{id}', UserController::class.':update');
    $this->delete('/users/{id}', UserController::class.':delete');
    
    // People resource
    $this->get('/people', PersonController::class.':list')->setName('people-list');
    $this->post('/people', PersonController::class.':create');
    $this->get('/people/{id}', PersonController::class.':show');
    $this->patch('/people/{id}', PersonController::class.':update');
    $this->delete('/people/{id}', PersonController::class.':delete');

    // Accounts resource
    $this->get('/accounts', AccountController::class.':list')->setName('accounts-list');
    $this->post('/accounts', AccountController::class.':create');
    $this->get('/accounts/{id}', AccountController::class.':show');
    $this->patch('/accounts/{id}', AccountController::class.':update');
    $this->delete('/accounts/{id}', AccountController::class.':delete');
});

// Apply middleware to route group
foreach ($routeGroupMiddlewares as $middleware) {
    $routeGroup->add($middleware);
}