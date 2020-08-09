<?php namespace Tranquility\System\Enums;

// Entity classes
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject;
use Tranquility\Data\Entities\BusinessObjects\AccountBusinessObject;
use Tranquility\Data\Entities\BusinessObjects\PersonBusinessObject;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject;
use Tranquility\Data\Entities\HistoricalBusinessObjects\AbstractHistoricalBusinessObject;
use Tranquility\Data\Entities\HistoricalBusinessObjects\PersonHistoricalBusinessObject;
use Tranquility\Data\Entities\HistoricalBusinessObjects\UserHistoricalBusinessObject;
use Tranquility\Data\Entities\HistoricalBusinessObjects\AccountHistoricalBusinessObject;
use Tranquility\Data\Entities\SystemObjects\OAuthAccessTokenSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthAuthorisationCodeSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject;
use Tranquility\Data\Entities\SystemObjects\OAuthRefreshTokenSystemObject;
use Tranquility\Data\Entities\SystemObjects\TagSystemObject;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject;

// Tranquility class libraries
use Tranquility\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of entity types
 *
 * @package Tranquility\System\Enums
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

    private static $_entityTypeDetails = array(
        // Business objects
        self::Entity      		=> array('classname' => AbstractBusinessObject::class,             'historicalClassname' => AbstractHistoricalBusinessObject::class),
        self::Person      		=> array('classname' => PersonBusinessObject::class,               'historicalClassname' => PersonHistoricalBusinessObject::class),
        self::User        	    => array('classname' => UserBusinessObject::class,                 'historicalClassname' => UserHistoricalBusinessObject::class),
        self::Account     		=> array('classname' => AccountBusinessObject::class,              'historicalClassname' => AccountHistoricalBusinessObject::class),

        // System objects
        self::Tag         		=> array('classname' => TagSystemObject::class,                    'historicalClassname' => null),
        self::AuditTransaction  => array('classname' => TransactionSystemObject::class,            'historicalClassname' => null),
        self::OAuthClient       => array('classname' => OAuthClientSystemObject::class,            'historicalClassname' => null),
        self::OAuthTokenAccess  => array('classname' => OAuthAccessTokenSystemObject::class,       'historicalClassname' => null),
        self::OAuthTokenRefresh => array('classname' => OAuthRefreshTokenSystemObject::class,      'historicalClassname' => null),
        self::OAuthCode         => array('classname' => OAuthAuthorisationCodeSystemObject::class, 'historicalClassname' => null)
    );

    /**
     * Get the entity class name for the specified entity type
     *
     * @param string $entityType
     * @return string
     */
    public static function getEntityClassname($entityType) {
        if (array_key_exists($entityType, self::$_entityTypeDetails) == false) {
            throw new \Exception("Unable to find details for entity type '".$entityType."'.");
        }

        return self::$_entityTypeDetails[$entityType]['classname'];
    } 
    
    /**
     * Get the class name of the Historical Business Object for the specified entity type
     *
     * @param string $entityType
     * @return string|null
     */
    public static function getHistoricalEntityClassname($entityType) {
        if (array_key_exists($entityType, self::$_entityTypeDetails) == false) {
            throw new \Exception("Unable to find details for entity type '".$entityType."'.");
        }

        return self::$_entityTypeDetails[$entityType]['historicalClassname'];
    }
}