<?php namespace Tranquility\Resources;

// Utility libraries
use Carbon\Carbon;

// ORM class libraries
use \Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;

class AuthResource extends AbstractResource {

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @return string
     */
    public function getEntityClassname() {
        throw \Exception("An entity classname is not defined for the 'AuthResource' class.");
    }

    public function findUser($userId) {
        $repository = $this->entityManager->getRepository(User::class);
        $searchOptions = array('id' => $userId);
        return $repository->findOneBy($searchOptions);
    }

    public function findClient($clientId) {
        $repository = $this->entityManager->getRepository(Client::class);
        $searchOptions = array('clientId' => $clientId);
        return $repository->findOneBy($searchOptions);
    }

    public function generateAuditTrail($data) {
        // Get audit trail details from authentication token
        $userId = Utility::extractValue($data, 'user_id', 0);
        $clientId = Utility::extractValue($data, 'client_id', 'invalid_client_id');

        // Build audit trail object
        $auditTrailData = [
            'user' => $this->findUser($userId),
            'client' => $this->findClient($clientId),
            'timestamp' => Carbon::now()
        ];
        $auditTrail = new AuditTrail($auditTrailData);
        return $auditTrail;
    }
}