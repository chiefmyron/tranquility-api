<?php namespace Tranquility\Data\Repositories\BusinessObjects;

// OAuth2 server libraries
use OAuth2\Storage\UserCredentialsInterface;

class UserBusinessObjectRepository extends BusinessObjectRepository implements UserCredentialsInterface {
    public function getUserDetails($username) {
        $user = $this->findOneBy(['username' => $username]);
        if ($user) {
            $user = $user->toArray();
        }
        return $user;
    }
    
    public function checkUserCredentials($username, $password) {
        $user = $this->findOneBy(['username' => $username]);
        if ($user) {
            return $user->verifyPassword($password);
        }
        return false;
    }
}