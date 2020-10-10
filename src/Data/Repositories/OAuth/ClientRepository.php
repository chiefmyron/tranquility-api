<?php namespace Tranquillity\Data\Repositories\OAuth;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// Vendor class libraries
use OAuth2\Storage\ClientCredentialsInterface;
use Tranquillity\Data\Entities\OAuth\ClientEntity;

class ClientRepository extends EntityRepository implements ClientCredentialsInterface {
    public function createClient($clientId, $clientSecret) {
        // Create new client record
        $data = ['clientName' => $clientId, 'clientSecret' => $clientSecret];
        $client = new ClientEntity($data);
        $this->_em->persist($client);
        $this->_em->flush();
		return $client;
    }
    
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