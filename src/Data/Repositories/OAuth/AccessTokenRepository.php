<?php namespace Tranquillity\Data\Repositories\OAuth;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// Vendor class libraries
use OAuth2\Storage\AccessTokenInterface;

// Entity classes
use Tranquillity\Data\Entities\OAuth\AccessTokenEntity as AccessToken;
use Tranquillity\Data\Entities\OAuth\ClientEntity as Client;
use Tranquillity\Data\Entities\Business\UserEntity as User;

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
        $client = $this->_em->getRepository(Client::class)->findOneBy(['clientName' => $clientId]);
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