<?php namespace Tranquility\Data\Repositories\SystemObjects;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// OAuth2 server libraries
use OAuth2\Storage\ClientCredentialsInterface;

class OAuthClientRepository extends EntityRepository implements ClientCredentialsInterface {
    public function getClientDetails($clientId) {
        $client = $this->findOneBy(['clientId' => $clientId]);
        if ($client) {
            $client = $client->toArray();
        }
        return $client;
    }

    public function checkClientCredentials($clientId, $clientSecret = null) {
        $client = $this->findOneBy(['clientId' => $clientId]);
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