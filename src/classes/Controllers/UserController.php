<?php namespace Tranquility\Controllers;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Services\UserService;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Transformers\UserTransformer;
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class UserController extends AbstractController {

    public function list($request, $response, $args) {
        // Extracting pagination information from the request, page = 1, size = 10 if it is missing
        //$pagination = $jsonApi->getRequest()->getPageBasedPagination(1, 10);

        // Retrieve users
        // TODO: Add parameters for pagination
        //$users = $this->service->all();

        // Generate response document
        //$document = $this->generateResourceCollectionDocument($users);
        //return $response->withJson($document, HttpStatus::OK);







        // Retrieve users
        $users = $this->service->all();

        // Transform for output
        $resource = new Collection($users, new UserTransformer, 'users');
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function show($request, $response, $args) {
        // Retrieve users
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $user = $this->service->find($id);
        if (!($user instanceof User)) {
            // If a user was not created, generate error response
            return $this->withErrorCollection($response, $user, HttpStatus::UnprocessableEntity);
        }

        // Transform for output
        $resource = new Item($user, new UserTransformer, 'users');
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_create_new_record';

        // Attempt to create the user entity
        $user = $this->service->create($payload);
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
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_update_existing_record';

        // Attempt to update the user entity
        $user = $this->service->update($id, $payload);
        if (!($user instanceof User)) {
            // If a user was not created, generate error response
            return $this->withErrorCollection($response, $user, HttpStatus::UnprocessableEntity);
        }

        // Transform for output
        $resource = new Item($user, new UserTransformer);
        $payload = $this->manager->createData($resource)->toArray();
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function delete($request, $response, $args) {

    }


}