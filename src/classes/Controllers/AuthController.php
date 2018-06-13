<?php namespace Tranquility\Controllers;

class AuthController {
    public function login($request, $response, $args) {
        $response->getBody()->write("This is the login service!");
        return $response;
    }
}