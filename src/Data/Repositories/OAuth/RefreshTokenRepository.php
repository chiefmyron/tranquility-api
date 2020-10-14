<?php declare(strict_types=1);
namespace Tranquillity\Data\Repositories\OAuth;

// Library classes
use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\RefreshTokenInterface;

// Application classes
use Tranquillity\Data\Entities\OAuth\RefreshTokenEntity;
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\Business\UserEntity;

class RefreshTokenRepository extends EntityRepository implements RefreshTokenInterface {
    public function getRefreshToken($refreshToken) {
        $token = $this->findOneBy(['token' => $refreshToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientEntity::class)->findOneBy(['clientName' => $clientId]);
        $user = $this->_em->getRepository(UserEntity::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $refreshToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new RefreshTokenEntity($tokenDetails);
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function unsetRefreshToken($refreshToken)
    {
        $refreshToken = $this->findOneBy(['refresh_token' => $refreshToken]);
        $this->_em->remove($refreshToken);
        $this->_em->flush();
    }
}