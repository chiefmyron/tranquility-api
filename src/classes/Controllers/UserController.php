<?php namespace Tranquility\Controllers;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Resources\UserResource;
use Tranquility\Transformers\UserTransformer;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class UserController extends AbstractController {

    public function list($request, $response, $args) {
        // Retrieve users
        $users = $this->resource->all();

        // Transform for output
        $resource = new Collection($users, new UserTransformer);
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $data = $this->parseRequestBody($request);

        // Construct audit trail for this request
        $audit = $request->getAttribute('audit');
        $audit->updateReason = 'testing';

        // Attempt to create the user entity
        $user = $this->resource->create($data, $audit);
        if (!($user instanceof User)) {
            // If a user was not created, generate error response
            return $this->withErrorCollection($response, $user, HttpStatus::UnprocessableEntity);
        }

        // Transform for output
        $resource = new Item($user, new UserTransformer);
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, HttpStatus::Created);
    }

    public function update($request, $response, $args) {

    }

    public function delete($request, $response, $args) {

    }


}