<?php namespace Tranquility\Controllers;

// Tranquility class libraries
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Services\UserService;
use Tranquility\Resources\UserResource;
use Tranquility\Resources\UserResourceCollection;
use Tranquility\Resources\ErrorResourceCollection;
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class UserController extends AbstractController {

    public function list($request, $response, $args) {
        // Retrieve users
        $params = $this->_parseQueryStringParams($request);
        $data = $this->service->all($params['filters'], $params['sorting'], $params['pagination']['pageNumber'], $params['pagination']['pageSize']);

        // Transform for output
        if (is_array($data) && count($data) > 0 && !($data[0] instanceof User)) {
            // Service has encountered an error
            $resource = new ErrorResourceCollection($data, $this->router);
            return $this->generateJsonResponse($request, $response, $resource, HttpStatus::UnprocessableEntity);
        }

        // Data is a collection of users
        $resource = new UserResourceCollection($data, $this->router);
        return $this->generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function show($request, $response, $args) {
        // Retrieve users
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $data = $this->service->find($id);

        // Transform for output
        if (!($data instanceof User)) {
            // Service has encountered an error
            $resource = new ErrorResourceCollection($data, $this->router);
            return $this->generateJsonResponse($request, $response, $resource, HttpStatus::UnprocessableEntity);
        }

        // Data is an instance of a user
        $resource = new UserResource($data, $this->router);
        return $this->generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_create_new_record';

        // Attempt to create the user entity
        $data = $this->service->create($payload);
        
        // Transform for output
        if (!($data instanceof User)) {
            // Service has encountered an error
            $resource = new ErrorResourceCollection($data, $this->router);
            return $this->generateJsonResponse($request, $response, $resource, HttpStatus::UnprocessableEntity);
        }

        // Data is an instance of a user
        $resource = new UserResource($data, $this->router);
        return $this->generateJsonResponse($request, $response, $resource, HttpStatus::Created);
    }

    public function update($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_update_existing_record';

        // Attempt to update the user entity
        $data = $this->service->update($id, $payload);
        
        // Transform for output
        if (!($data instanceof User)) {
            // Service has encountered an error
            $resource = new ErrorResourceCollection($data, $this->router);
            return $this->generateJsonResponse($request, $response, $resource, HttpStatus::UnprocessableEntity);
        }

        // Data is an instance of a user
        $resource = new UserResource($data, $this->router);
        return $this->generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function delete($request, $response, $args) {

    }
}