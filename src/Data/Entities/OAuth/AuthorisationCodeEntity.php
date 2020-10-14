<?php declare(strict_types=1);
namespace Tranquillity\Data\Entities\OAuth;

// Library classes
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Application classes
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\Business\UserEntity;
use Tranquillity\Data\Repositories\OAuth\AuthorisationCodeRepository;
use Tranquillity\System\Enums\EntityTypeEnum;

class AuthorisationCodeEntity extends AbstractOAuthEntity {
    // Entity properties
    protected $id;
    protected $code;
    protected $expires;
    protected $redirectUri;
    protected $scope;

    // Related entities
    protected $client;
    protected $user;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'id'          => ['type' => 'integer',  'visibility' => 'public', 'auditable' => false],
        'code'        => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
        'expires'     => ['type' => 'datetime', 'visibility' => 'public', 'auditable' => false],
        'redirectUri' => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
        'scope'       => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'client' => ['type' => EntityTypeEnum::OAuthClient, 'visibility' => 'public', 'collection' => false],
        'user'   => ['type' => EntityTypeEnum::User, 'visibility' => 'public', 'collection' => false]
    ];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::OAuthCode;
    }

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return self::$_entityFieldDefinitions;
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return self::$_entityRelationshipDefinitions;
    }

    /**
     * Cast authorisation code to an array for use in the OAuth library
     *
     * @return array
     */
    public function toArray() {
        return [
            'code' => $this->code,
            'client_id' => $this->client->clientId,  // Key needs to be in underscore format for OAuth library
            'user_id' => $this->user->id,            // Key needs to be in underscore format for OAuth library
            'expires' => $this->expires,
            'scope' => $this->scope
        ];
    }

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);

        // Define table name
        $builder->setTable('auth_authorisation_codes');
        $builder->setCustomRepositoryClass(AuthorisationCodeRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->makePrimaryKey()->generatedValue()->build();
        $builder->addField('code', 'string');
        $builder->addField('expires', 'datetime');
        $builder->addField('redirectUri', 'string');
        $builder->addField('scope', 'string');

        // Define relationships
        $builder->createManyToOne('client', ClientEntity::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', UserEntity::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}