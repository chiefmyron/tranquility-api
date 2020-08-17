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
use Tranquility\Controllers\TagController;

// Note to future self - DON'T TRY TO GET CLEVER WITH AUTO-GENERATING ROUTES
// See: https://phil.tech/php/2013/07/23/beware-the-route-to-evil/
// "routes.php is documentation"

return function (App $app) {
    // Version 1 API routes (unauthenticated)
    $app->get('/', RootController::class.':home');
    $app->post('/v1/auth/token', AuthController::class.':token');

    // Version 1 API route group (authenticated)
    $routeGroup = $app->group('/v1', function(RouteCollectorProxy $group) {
        // Tag resource
        $group->get('/tags', TagController::class.':list')->setName('tag-list');
        $group->post('/tags', TagController::class.':create');
        $group->get('/tags/{id}', TagController::class.':show')->setName('tag-detail');
        $group->patch('/tags/{id}', TagController::class.':update');
        $group->delete('/tags/{id}', TagController::class.':delete');
        $group->get('/tags/{id}/{resource}', TagController::class.':showRelated')->setName('tag-related');
        $group->get('/tags/{id}/relationships/{resource}', TagController::class.':showRelationship')->setName('tag-relationships');
        $group->post('/tags/{id}/relationships/{resource}', TagController::class.':addRelationship');
        $group->patch('/tags/{id}/relationships/{resource}', TagController::class.':updateRelationship');
        $group->delete('/tags/{id}/relationships/{resource}', TagController::class.':deleteRelationship');
        
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
        $group->post('/people/{id}/relationships/{resource}', PersonController::class.':addRelationship');
        $group->patch('/people/{id}/relationships/{resource}', PersonController::class.':updateRelationship');
        $group->delete('/people/{id}/relationships/{resource}', PersonController::class.':deleteRelationship');

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