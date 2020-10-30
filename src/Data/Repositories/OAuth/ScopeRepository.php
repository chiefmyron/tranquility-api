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
        // Check that each scope requested actually exists
        $scopes = explode(" ", $scope);
        foreach ($scopes as $scopeName) {
            $scope = $this->findOneBy(['scope' => $scopeName]);
            if (is_null($scope) === true) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultScope($client_id = null) {
        // Force the client to request scope
        return false;
    }
}