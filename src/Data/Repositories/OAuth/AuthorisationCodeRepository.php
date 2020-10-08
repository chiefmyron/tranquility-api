<?php namespace Tranquillity\Data\Repositories\OAuth;

// ORM class libraries
use Doctrine\ORM\EntityRepository;

// Vendor class libraries
use OAuth2\Storage\AuthorizationCodeInterface;

// Entity classes
use Tranquillity\Data\Entities\OAuth\AuthorisationCodeEntity as AuthorisationCode;
use Tranquillity\Data\Entities\OAuth\ClientEntity as Client;
use Tranquillity\Data\Entities\Business\UserEntity as User;

class AuthorisationCodeRepository extends EntityRepository implements AuthorizationCodeInterface {
    public function getAuthorizationCode($code) {
        $authCode = $this->findOneBy(['code' => $code]);
        if ($authCode) {
            $authCode = $authCode->toArray();
            $authCode['expires'] = $authCode['expires']->getTimestamp();
        }
        return $authCode;
    }

    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null) {
        $client = $this->_em->getRepository(Client::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(User::class)->findOneBy(['id' => $userId]);

        // Generate and store authorisation code
        $authCodeDetails = [
            'code' => $code,
            'client' => $client,
            'user' => $user,
            'redirectUri' => $redirectUri,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $authCode = new AuthorisationCode($authCodeDetails);
        $this->_em->persist($authCode);
        $this->_em->flush();
    }

    public function expireAuthorizationCode($code) {
        $authCode = $this->findOneBy(['code' => $code]);
        $this->_em->remove($authCode);
        $this->_em->flush();
    }
}