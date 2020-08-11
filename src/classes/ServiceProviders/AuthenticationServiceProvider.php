<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Vendor class libraries
use DI;
use DI\ContainerBuilder;
use OAuth2\Server as OAuth2Server;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;

// Framework class libraries
use Tranquility\Controllers\AuthController;
use Tranquility\Middlewares\AuthenticationMiddleware;
use Tranquility\Data\Entities\OAuth\AccessTokenEntity;
use Tranquility\Data\Entities\OAuth\AuthorisationCodeEntity;
use Tranquility\Data\Entities\OAuth\ClientEntity;
use Tranquility\Data\Entities\OAuth\RefreshTokenEntity;
use Tranquility\Data\Entities\Business\UserEntity;

class AuthenticationServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder, string $name) {
        $containerBuilder->addDefinitions([
            // Register OAuth2 server with the container
            OAuth2Server::class => function(ContainerInterface $c) {
                // Get entities used to represent OAuth objects
                $em = $c->get('em');
                $clientStorage = $em->getRepository(ClientEntity::class);
                $userStorage = $em->getRepository(UserEntity::class);
                $accessTokenStorage = $em->getRepository(AccessTokenEntity::class);
                $refreshTokenStorage = $em->getRepository(RefreshTokenEntity::class);
                $authorisationCodeStorage = $em->getRepository(AuthorisationCodeEntity::class);
    
                // Create OAuth2 server
                $storage = [
                    'client_credentials' => $clientStorage,
                    'user_credentials'   => $userStorage,
                    'access_token'       => $accessTokenStorage,
                    'refresh_token'      => $refreshTokenStorage,
                    'authorization_code' => $authorisationCodeStorage
                ];
                $server = new OAuth2Server($storage, ['auth_code_lifetime' => 30, 'refresh_token_lifetime' => 30]);
    
                // Add grant types
                $server->addGrantType(new ClientCredentials($clientStorage));
                $server->addGrantType(new UserCredentials($userStorage));
                $server->addGrantType(new AuthorizationCode($authorisationCodeStorage));
                $server->addGrantType(new RefreshToken($refreshTokenStorage, ['always_issue_new_refresh_token' => true]));
    
                return $server;
            },

            // Register authentication middleware with the container
            AuthenticationMiddleware::class => DI\create()->constructor(DI\get(OAuth2Server::class)),

            // Register authentication controller with the container
            AuthController::class => DI\create()->constructor(DI\get(OAuth2Server::class))
        ]);
    }
}