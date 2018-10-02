<?php namespace Tranquility\Data\Repositories\OAuth;

use OAuth2\Storage\ClientCredentialsInterface;
use Tranquility\Data\Repositories\AbstractRepository;

class ClientOAuthRepository extends AbstractRepository implements ClientCredentialsInterface {
    public function getClientDetails($clientId) {
        $client = $this->findOneBy(['clientId' => $clientId]);
        if ($client) {
            $client = $client->toArray();
        }
        return $client;
    }

    public function checkClientCredentials($clientIdentifier, $clientSecret = null) {
        $client = $this->findByOne(['clientId' => $clientId]);
        if ($client) {
            return $client->verifyClientSecret($clientSecret);
        }
        return false;
    }

    public function checkRestrictedGrantType($clientId, $grantType) {
        return true;
    }

    public function isPublicClient($clientId) {
        return false;
    }

    public function getClientScope($clientId) {
        return null;
    }
}