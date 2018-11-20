<?php namespace Tranquility\Data\Repositories\SystemObjects;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// OAuth2 server libraries
use OAuth2\Storage\AccessTokenInterface;

// Tranquility class libraries
use Tranquility\Data\Entities\SystemObjects\OAuthAccessTokenSystemObject as AccessToken;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class OAuthAccessTokenRepository extends EntityRepository implements AccessTokenInterface {
    public function getAccessToken($oauthToken) {
        $token = $this->findOneBy(['token' => $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null) {
        $client = $this->_em->getRepository(Client::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(User::class)->findOneBy(['id' => $userId]);

        // Generate and store token
        $tokenDetails = [
            'token' => $oauthToken,
            'client' => $client,
            'user' => $user,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $token = new AccessToken($tokenDetails);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}