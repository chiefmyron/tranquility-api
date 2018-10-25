<?php namespace Tranquility\Data\Repositories\OAuth;

use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\AuthorizationCodeInterface;

use Tranquility\Data\Entities\OAuth\AuthorisationCodeOAuth;
use Tranquility\Data\Entities\OAuth\ClientOAuth;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;

class AuthorisationCodeOAuthRepository extends EntityRepository implements AuthorizationCodeInterface {
    public function getAuthorizationCode($code) {
        $authCode = $this->findOneBy(['code' => $code]);
        if ($authCode) {
            $authCode = $authCode->toArray();
            $authCode['expires'] = $authCode['expires']->getTimestamp();
        }
        return $authCode;
    }

    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null) {
        $client = $this->_em->getRepository(ClientOAuth::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(UserBusinessObject::class)->findOneBy(['id' => $userId]);

        // Generate and store authorisation code
        $authCodeDetails = [
            'code' => $code,
            'client' => $client,
            'user' => $user,
            'redirectUri' => $redirectUri,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $authCode = new AuthorisationCodeOAuth($authCodeDetails);
        $this->_em->persist($authCode);
        $this->_em->flush();
    }

    public function expireAuthorizationCode($code) {
        $authCode = $this->findOneBy(['code' => $code]);
        $this->_em->remove($authCode);
        $this->_em->flush();
    }
}