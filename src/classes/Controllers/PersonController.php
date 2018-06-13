<?php namespace Tranquility\Controllers;

class PersonController {
    public function index($request, $response, $args) {
        $response->getBody()->write("This is the Person controller!");
        return $response;
    }
}