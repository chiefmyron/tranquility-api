<?php namespace Tranquility\Controllers;

use \Tranquility\Resources\AccountResource;

class AccountController {

    private $accountsResource;

    public function __construct(AccountResource $accountsResource) {
        $this->accountsResource = $accountsResource;
    }

    public function list($request, $response, $args) {
        return $this->accountsResource->all();
    }

    public function create($request, $response, $args) {

    }

    public function update($request, $response, $args) {

    }

    public function delete($request, $response, $args) {

    }
}