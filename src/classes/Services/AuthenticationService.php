<?php namespace Tranquility\Services;

// OAuth Server library
use OAuth2\Server;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;

// Tranquility OAuth entities
use Tranquility\Data\Entities\SystemObjects\OAuthAccessTokenSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthAuthorisationCodeSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthRefreshTokenSystemObject;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;

// Tranquility middlewares
use Tranquility\Middlewares\AuthenticationMiddleware;

class AuthenticationService extends AbstractService {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // Register OAuth2 server with the container
        $container[Server::class] = function($c) {
            // Get entities used to represent OAuth objects
            $em = $c->get('em');
            $clientStorage = $em->getRepository(OAuthClientSystemObject::class);
            $userStorage = $em->getRepository(UserBusinessObject::class);
            $accessTokenStorage = $em->getRepository(OAuthAccessTokenSystemObject::class);
            $refreshTokenStorage = $em->getRepository(OAuthRefreshTokenSystemObject::class);
            $authorisationCodeStorage = $em->getRepository(OAuthAuthorisationCodeSystemObject::class);

            // Create OAuth2 server
            $storage = [
                'client_credentials' => $clientStorage,
                'user_credentials'   => $userStorage,
                'access_token'       => $accessTokenStorage,
                'refresh_token'      => $refreshTokenStorage,
                'authorization_code' => $authorisationCodeStorage
            ];
            $server = new Server($storage, ['auth_code_lifetime' => 30, 'refresh_token_lifetime' => 30]);

            // Add grant types
            $server->addGrantType(new ClientCredentials($clientStorage));
            $server->addGrantType(new UserCredentials($userStorage));
            $server->addGrantType(new AuthorizationCode($authorisationCodeStorage));
            $server->addGrantType(new RefreshToken($refreshTokenStorage, ['always_issue_new_refresh_token' => true]));

            return $server;
        };

        // Register authentication middleware with the container
        $container[AuthenticationMiddleware::class] = function($c) {
            $em = $c->get('em');
            $server = $c->get(Server::class);
            return new AuthenticationMiddleware($server);
        };
    }
}