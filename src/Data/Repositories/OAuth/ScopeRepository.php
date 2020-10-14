<?php declare(strict_types=1);
namespace Tranquillity\Data\Repositories\OAuth;

// Library classes
use Doctrine\ORM\EntityRepository;
use OAuth2\Storage\ScopeInterface;

class ScopeRepository extends EntityRepository implements ScopeInterface {

    /**
     * {@inheritDoc}
     */
    public function scopeExists($scope) {
        $scope = $this->findOneBy(['scope' => $scope]);
        if ($scope) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultScope($client_id = null) {
        // Force the client to request scope
        return false;
    }
}