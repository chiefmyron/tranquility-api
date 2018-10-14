<?php namespace Tranquility\Data\Repositories\OAuth;

use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\AccessTokenInterface;

use Tranquility\Data\Entities\OAuth\AccessTokenOAuth;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;

class AccessTokenOAuthRepository extends EntityRepository implements AccessTokenInterface {
    public function getAccessToken($oauthToken) {
        $token = $this->findOneBy(['token' -> $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientOAuth::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(UserBusinessObject::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $oauthToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new AccessTokenOAuth($tokenDetails);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}