<?php namespace Tranquility\Data\Repositories\SystemObjects;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// OAuth2 server libraries
use OAuth2\Storage\RefreshTokenInterface;

// Tranquility class libraries
use Tranquility\Data\Entities\SystemObjects\OAuthRefreshTokenSystemObject as RefreshToken;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class OAuthRefreshTokenRepository extends EntityRepository implements RefreshTokenInterface {
    public function getRefreshToken($refreshToken) {
        $token = $this->findOneBy(['token' => $refreshToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(Client::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(User::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $refreshToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new RefreshToken($tokenDetails);
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