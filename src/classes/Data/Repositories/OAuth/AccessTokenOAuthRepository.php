<?php namespace Tranquility\Data\Repositories\OAuth;

use OAuth2\Storage\AccessTokenInterface;
use Tranquility\Data\Entities\OAuth\AccessTokenOAuth;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;

class AccessTokenOAuthRepository extends BusinessObjectRepository implements AccessTokenInterface {
    public function getAccessToken($oauthToken) {
        $token = $this->findOneBy(['token' -> $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientId, $username, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientOAuth::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(UserBusinessObject::class)->findOneBy(['username' => $username]);

        // Generate and store token
        $token = AccessTokenOAuth::fromArray([
            'token' => $oauthToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ]);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}