<?php namespace Tranquility\Data\Repositories\OAuth;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// Vendor class libraries
use OAuth2\Storage\ClientCredentialsInterface;

class ClientRepository extends EntityRepository implements ClientCredentialsInterface {
    public function getClientDetails($clientId) {
        $client = $this->findOneBy(['clientName' => $clientId]);
        if ($client) {
            $client = $client->toArray();
        }
        return $client;
    }

    public function checkClientCredentials($clientId, $clientSecret = null) {
        $client = $this->findOneBy(['clientName' => $clientId]);
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