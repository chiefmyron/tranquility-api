<?php declare(strict_types=1);
namespace Tranquillity\Data\Repositories\OAuth;

// Library classes
use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\AuthorizationCodeInterface;

// Application classes
use Tranquillity\Data\Entities\OAuth\AuthorisationCodeEntity;
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\Business\UserEntity;

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
        $client = $this->_em->getRepository(ClientEntity::class)->findOneBy(['clientId' => $clientId]);
        $user = $this->_em->getRepository(UserEntity::class)->findOneBy(['id' => $userId]);

        // Generate and store authorisation code
        $authCodeDetails = [
            'code' => $code,
            'client' => $client,
            'user' => $user,
            'redirectUri' => $redirectUri,
            'expires' => (new \DateTime())->setTimestamp($expires),
            'scope' => $scope
        ];
        $authCode = new AuthorisationCodeEntity($authCodeDetails);
        $this->_em->persist($authCode);
        $this->_em->flush();
    }

    public function expireAuthorizationCode($code) {
        $authCode = $this->findOneBy(['code' => $code]);
        $this->_em->remove($authCode);
        $this->_em->flush();
    }
}