<?php

declare(strict_types=1);

// Framework classes
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

// Tranquility route-specific middlewares
use Tranquility\Middlewares\AuthenticationMiddleware;
use Tranquility\Middlewares\JsonApiRequestValidatorMiddleware;

// Tranquility controllers
use Tranquility\Controllers\RootController;
use Tranquility\Controllers\AuthController;
use Tranquility\Controllers\UserController;
use Tranquility\Controllers\PersonController;
use Tranquility\Controllers\AccountController;

return function (App $app) {
    // Version 1 API routes (unauthenticated)
    $app->get('/', RootController::class.':home');
    $app->post('/v1/auth/token', AuthController::class.':token');

    // Version 1 API route group (authenticated)
    $routeGroup = $app->group('/v1', function(RouteCollectorProxy $group) {
        // Audit trail resource
        $group->get('/transactions', UserController::class.':list')->setName('transaction-list');
        $group->post('/transactions', UserController::class.':create');
        $group->get('/transactions/{id}', UserController::class.':show')->setName('transaction-detail');
        $group->patch('/transactions/{id}', UserController::class.':update');
        $group->delete('/transactions/{id}', UserController::class.':delete');
        $group->get('/transactions/{id}/{resource}', UserController::class.':showRelated')->setName('transaction-related');
        $group->get('/transactions/{id}/relationships/{resource}', UserController::class.':showRelationship')->setName('transaction-relationships');
        
        // User resource
        $group->get('/users', UserController::class.':list')->setName('user-list');
        $group->post('/users', UserController::class.':create');
        $group->get('/users/{id}', UserController::class.':show')->setName('user-detail');
        $group->patch('/users/{id}', UserController::class.':update');
        $group->delete('/users/{id}', UserController::class.':delete');
        $group->get('/users/{id}/{resource}', UserController::class.':showRelated')->setName('user-related');
        $group->get('/users/{id}/relationships/{resource}', UserController::class.':showRelationship')->setName('user-relationships');
        $group->post('/users/{id}/relationships/{resource}', UserController::class.':addRelationship');
        $group->patch('/users/{id}/relationships/{resource}', UserController::class.':updateRelationship');
        $group->delete('/users/{id}/relationships/{resource}', UserController::class.':deleteRelationship');
        
        // People resource
        $group->get('/people', PersonController::class.':list')->setName('person-list');
        $group->post('/people', PersonController::class.':create');
        $group->get('/people/{id}', PersonController::class.':show')->setName('person-detail');
        $group->patch('/people/{id}', PersonController::class.':update');
        $group->delete('/people/{id}', PersonController::class.':delete');
        $group->get('/people/{id}/{resource}', PersonController::class.':showRelated')->setName('person-related');
        $group->get('/people/{id}/relationships/{resource}', PersonController::class.':showRelationship')->setName('person-relationships');

        // Accounts resource
        $group->get('/accounts', AccountController::class.':list')->setName('accounts-list');
        $group->post('/accounts', AccountController::class.':create');
        $group->get('/accounts/{id}', AccountController::class.':show');
        $group->patch('/accounts/{id}', AccountController::class.':update');
        $group->delete('/accounts/{id}', AccountController::class.':delete');
    });

    // Version 1 API route group (authenticated) middleware
    $routeMiddleware = [
        AuthenticationMiddleware::class,
        JsonApiRequestValidatorMiddleware::class
    ];
    foreach ($routeMiddleware as $middleware) {
        $routeGroup->add($middleware);
    }
};