<?php namespace Tranquility\Controllers;

use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class AuthController extends AbstractController {
    public function login($request, $response, $args) {
        // Attempt to login user
        $data = $this->parseRequestBody($request);
        $result = $this->resource->login($data);

        return $response->withJson($result, HttpStatus::Created);
    }

    public function logout($request, $response, $args) {

    }


}