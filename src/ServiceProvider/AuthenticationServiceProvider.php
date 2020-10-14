<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;
use OAuth2\Scope;
// Application classes
use Tranquillity\Data\Entities\OAuth\AccessTokenEntity;
use Tranquillity\Data\Entities\OAuth\AuthorisationCodeEntity;
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\OAuth\RefreshTokenEntity;
use Tranquillity\Data\Entities\Business\UserEntity;
use Tranquillity\Data\Entities\OAuth\ScopeEntity;

class AuthenticationServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            // Register OAuth2 server with the container
            OAuth2Server::class => function(ContainerInterface $c) {
                // Get entities used to represent OAuth objects
                $em = $c->get(EntityManagerInterface::class);
                $clientStorage = $em->getRepository(ClientEntity::class);
                $userStorage = $em->getRepository(UserEntity::class);
                $accessTokenStorage = $em->getRepository(AccessTokenEntity::class);
                $refreshTokenStorage = $em->getRepository(RefreshTokenEntity::class);
                $authorisationCodeStorage = $em->getRepository(AuthorisationCodeEntity::class);
                $scopeStorage = $em->getRepository(ScopeEntity::class);
    
                // Create OAuth2 server
                $storage = [
                    'client_credentials' => $clientStorage,
                    'user_credentials'   => $userStorage,
                    'access_token'       => $accessTokenStorage,
                    'refresh_token'      => $refreshTokenStorage,
                    'authorization_code' => $authorisationCodeStorage
                ];
                $server = new OAuth2Server($storage, ['auth_code_lifetime' => 30, 'refresh_token_lifetime' => 30]);
                
                // Create scope storage manager
                $scope = new Scope($scopeStorage);
                $server->setScopeUtil($scope);
    
                // Add grant types
                $server->addGrantType(new ClientCredentials($clientStorage));
                $server->addGrantType(new UserCredentials($userStorage));
                $server->addGrantType(new AuthorizationCode($authorisationCodeStorage));
                $server->addGrantType(new RefreshToken($refreshTokenStorage, ['always_issue_new_refresh_token' => true]));
    
                return $server;
            }
        ]);
    }
}