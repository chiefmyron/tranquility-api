<?php namespace Tranquility\Controllers;

// Tranquility class libraries
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Services\UserService;
use Tranquility\Resources\UserResource;
use Tranquility\Resources\UserResourceCollection;
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class UserController extends AbstractController {

    public function list($request, $response, $args) {
        // Retrieve users
        // TODO: Add parameters for pagination
        $users = $this->service->all();

        // Transform for output
        $payload = $this->generateResponse($request, $users);
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function show($request, $response, $args) {
        // Retrieve users
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $user = $this->service->find($id);

        // Transform for output
        $payload = $this->generateResponse($request, $user);
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_create_new_record';

        // Attempt to create the user entity
        $user = $this->service->create($payload);
        
        // Transform for output
        $payload = $this->generateResponse($request, $user);
        return $response->withJson($payload, HttpStatus::Created);
    }

    public function update($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_update_existing_record';

        // Attempt to update the user entity
        $user = $this->service->update($id, $payload);
        
        // Transform for output
        $payload = $this->generateResponse($request, $user);
        return $response->withJson($payload, HttpStatus::OK);
    }

    public function delete($request, $response, $args) {

    }

    private function generateResponse($request, $data) {
        if ($data instanceof User) {
            // Data is an instance of a user
            $resource = new UserResource($data, $this->router);
            return $resource->toResponseArray($request);
        }

        if (is_array($data) && count($data) > 0 && ($data[0] instanceof User)) {
            // Data is a collection of users
            $resource = new UserResourceCollection($data, $this->router);
            return $resource->toResponseArray($request);
        } 

        // If we reach this point, an error collection has been provided
        return $this->withErrorCollection($response, $data, HttpStatus::UnprocessableEntity);
    }


}