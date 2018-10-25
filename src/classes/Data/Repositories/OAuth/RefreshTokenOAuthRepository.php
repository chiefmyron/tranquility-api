<?php namespace Tranquility\Data\Repositories\OAuth;

use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\RefreshTokenInterface;

use Tranquility\Data\Entities\OAuth\RefreshTokenOAuth;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;

class RefreshTokenOAuthRepository extends EntityRepository implements RefreshTokenInterface {
    public function getRefreshToken($refreshToken) {
        $token = $this->findOneBy(['token' => $refreshToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setRefreshToken($refreshToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientOAuth::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(UserBusinessObject::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $refreshToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new RefreshTokenOAuth($tokenDetails);
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