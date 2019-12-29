<?php
// Tranquility route-specific middlewares
use Tranquility\Middlewares\AuthenticationMiddleware;
use Tranquility\Middlewares\JsonApiRequestValidatorMiddleware;

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
    JsonApiRequestValidatorMiddleware::class
];

// Version 1 API route group (authenticated)
$routeGroup = $app->group('/v1', function() {
    // Audit trail resource
    $this->get('/transations', UserController::class.':list')->setName('transaction-list');
    $this->post('/transactions', UserController::class.':create');
    $this->get('/transactions/{id}', UserController::class.':show')->setName('transaction-detail');
    $this->patch('/transactions/{id}', UserController::class.':update');
    $this->delete('/transactions/{id}', UserController::class.':delete');
    $this->get('/transactions/{id}/related/{resource}', UserController::class.':showRelated')->setName('transaction-related');
    $this->get('/transactions/{id}/relationships/{resource}', UserController::class.':showRelationship')->setName('transaction-relationships');
    
    // User resource
    $this->get('/users', UserController::class.':list')->setName('user-list');
    $this->post('/users', UserController::class.':create');
    $this->get('/users/{id}', UserController::class.':show')->setName('user-detail');
    $this->patch('/users/{id}', UserController::class.':update');
    $this->delete('/users/{id}', UserController::class.':delete');
    $this->get('/users/{id}/related/{resource}', UserController::class.':showRelated')->setName('user-related');
    $this->get('/users/{id}/relationships/{resource}', UserController::class.':showRelationship')->setName('user-relationships');
    
    // People resource
    $this->get('/people', PersonController::class.':list')->setName('person-list');
    $this->post('/people', PersonController::class.':create');
    $this->get('/people/{id}', PersonController::class.':show')->setName('person-detail');
    $this->patch('/people/{id}', PersonController::class.':update');
    $this->delete('/people/{id}', PersonController::class.':delete');
    $this->get('/people/{id}/related/{resource}', PersonController::class.':showRelated')->setName('person-related');
    $this->get('/people/{id}/relationships/{resource}', PersonController::class.':showRelationship')->setName('person-relationships');

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