<?php namespace Tranquility\Controllers;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Data\Entities\UserEntity;
use Tranquility\Resources\UserResource;
use Tranquility\Transformers\UserTransformer;

class UserController extends AbstractController {

    public function list($request, $response, $args) {
        // Retrieve users
        $users = $this->resource->all();

        // Transform for output
        $resource = new Collection($users, new UserTransformer);
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, 201);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $data = $request->getParsedBody();
        $user = $this->resource->create($data);
        if (!($user instanceof UserEntity)) {
            // If a user was not created, generate error response
            return $this->generateValidationErrorResponse($response, $user);
        }

        // Transform for output
        $resource = new Item($user, new UserTransformer);
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, 201);
    }

    public function update($request, $response, $args) {

    }

    public function delete($request, $response, $args) {

    }
}