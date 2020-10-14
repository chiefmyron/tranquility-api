<?php namespace Tranquillity\System\Enums;

// Entity classes
use Tranquillity\Data\Entities\Business\AbstractBusinessEntity;
use Tranquillity\Data\Entities\Business\AccountEntity;
use Tranquillity\Data\Entities\Business\PersonEntity;
use Tranquillity\Data\Entities\Business\UserEntity;
use Tranquillity\Data\Entities\OAuth\AccessTokenEntity;
use Tranquillity\Data\Entities\OAuth\AuthorisationCodeEntity;
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\OAuth\RefreshTokenEntity;
use Tranquillity\Data\Entities\System\AuditTransactionEntity;
use Tranquillity\Data\Entities\System\AuditTransactionFieldEntity;
use Tranquillity\Data\Entities\System\TagEntity;

// Tranquillity class libraries
use Tranquillity\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of entity types
 *
 * @package Tranquillity\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class EntityTypeEnum extends AbstractEnum {
    // Business objects
    const Entity            = 'entity';
    const Person            = 'person';
    const User              = 'user';
    const Account           = 'account';
    const Address           = 'address';

    // System objects
    const Tag                   = 'tag';
    const AuditTransaction      = 'auditTransaction';
    const AuditTransactionField = 'auditTransactionField';

    // OAuth objects
    const OAuthClient       = 'oauthClient';
    const OAuthTokenAccess  = 'oauthTokenAccess';
    const OAuthTokenRefresh = 'oauthTokenRefresh';
    const OAuthCode         = 'oauthCode';
    const OAuthScope        = 'oauthScope';

    private static $_entityClasses = array(
        // Business objects
        self::Entity      		=> AbstractBusinessEntity::class,           
        self::Person      		=> PersonEntity::class,             
        self::User        	    => UserEntity::class,               
        self::Account     		=> AccountEntity::class,            

        // System objects
        self::Tag         		=> TagEntity::class,                  
        self::AuditTransaction  => AuditTransactionEntity::class,          
        self::AuditTransactionField => AuditTransactionFieldEntity::class,

        // OAuth objects
        self::OAuthClient       => ClientEntity ::class,          
        self::OAuthTokenAccess  => AccessTokenEntity::class,     
        self::OAuthTokenRefresh => RefreshTokenEntity::class,    
        self::OAuthCode         => AuthorisationCodeEntity::class
    );

    /**
     * Get the entity class name for the specified entity type
     *
     * @param string $entityType
     * @return string
     */
    public static function getEntityClassname($entityType) {
        if (array_key_exists($entityType, self::$_entityClasses) == false) {
            throw new \Exception("Unable to find classname for entity type '".$entityType."'.");
        }

        return self::$_entityClasses[$entityType];
    } 
}