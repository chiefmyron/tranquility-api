<?php declare(strict_types=1);
namespace Tranquillity\Data\Repositories\OAuth;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// Vendor class libraries
use OAuth2\Storage\AccessTokenInterface;

// Entity classes
use Tranquillity\Data\Entities\OAuth\AccessTokenEntity;
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\Business\UserEntity;

class AccessTokenRepository extends EntityRepository implements AccessTokenInterface {
    public function getAccessToken($oauthToken) {
        $token = $this->findOneBy(['token' => $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientEntity::class)->findOneBy(['clientName' => $clientId]);
        $user = $this->_em->getRepository(UserEntity::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $oauthToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new AccessTokenEntity($tokenDetails);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}