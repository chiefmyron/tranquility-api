<?php namespace Tranquility\Data\Entities\OAuth;

// Vendor class libraries
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\OAuth\ClientEntity as Client;
use Tranquility\Data\Entities\Business\UserEntity as User;
use Tranquility\Data\Repositories\OAuth\RefreshTokenRepository;

// Tranquility class libraries
use Tranquility\System\Utility;
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;

class RefreshTokenEntity extends AbstractOAuthEntity {
    // Entity properties
    protected $id;
    protected $token;
    protected $expires;
    protected $scope;

    // Related entities
    protected $client;
    protected $user;
    
    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'id'      => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
        'token'   => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
        'expires' => ['type' => 'datetime', 'visibility' => 'public', 'auditable' => false],
        'scope'   => ['type' => 'text',     'visibility' => 'public', 'auditable' => false],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'client' => ['type' => EntityTypeEnum::OAuthClient, 'visibility' => 'public', 'collection' => false],
        'user'   => ['type' => EntityTypeEnum::User, 'visibility' => 'public', 'collection' => false]
    ];

    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Set values for valid properties
        parent::__construct($data, $options);

        // Ensure entity identifiers are set
        if (!isset($this->id)) {
            $this->id = Utility::generateUuid(1);
        }
    }

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::OAuthTokenRefresh;
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
     * Cast refresh token to an array for use in the OAuth library
     *
     * @return array
     */
    public function toArray() {
        return [
            'refresh_token' => $this->token,
            'client_id' => $this->client->clientName,  // Key needs to be in underscore format for OAuth library
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
        $builder->setTable('auth_tokens_refresh');
        $builder->setCustomRepositoryClass(RefreshTokenRepository::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->createField('token', 'string')->length(40)->build();
        $builder->createField('scope', 'string')->length(4000)->nullable(true)->build();
        $builder->addField('expires', 'datetime');

        // Define relationships
        $builder->createManyToOne('client', Client::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', User::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}