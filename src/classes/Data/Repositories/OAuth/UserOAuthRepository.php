<?php namespace Tranquility\Data\Repositories\OAuth;

use OAuth2\Storage\UserCredentialsInterface;
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;

class UserOAuthRepository extends BusinessObjectRepository implements UserCredentialsInterface {
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