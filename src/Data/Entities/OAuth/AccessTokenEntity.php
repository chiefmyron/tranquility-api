<?php declare(strict_types=1);
namespace Tranquillity\Data\Entities\OAuth;

// Library classes
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Application classes
use Tranquillity\Data\Entities\OAuth\ClientEntity;
use Tranquillity\Data\Entities\Business\UserEntity;
use Tranquillity\Data\Repositories\OAuth\AccessTokenRepository;
use Tranquillity\System\Utility;
use Tranquillity\System\Enums\EntityTypeEnum;

class AccessTokenEntity extends AbstractOAuthEntity {
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
        'scope'   => ['type' => 'string',   'visibility' => 'public', 'auditable' => false],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'client' => ['type' => EntityTypeEnum::OAuthClient, 'visibility' => 'public', 'collection' => false],
        'user'   => ['type' => EntityTypeEnum::User,        'visibility' => 'public', 'collection' => false]
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
        return EntityTypeEnum::OAuthTokenAccess;
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

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function setClient(ClientEntity $client = null) {
        $this->client = $client;
        return $this;
    }

    public function setUser(UserEntity $user = null) {
        $this->user = $user;
        return $this;
    }

    public function setExpires($expires) {
        $this->expires = $expires;
        return $this;
    }

    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Cast access token to an array for use in the OAuth library
     *
     * @return array
     */
    public function toArray() {
        $token = [
            'token' => $this->token,
            'client_id' => $this->client->clientName,  // Key needs to be in underscore format for OAuth library
            'expires' => $this->expires,
            'scope' => $this->scope
        ];

        // If token is associated with a specific user, add the user ID as well
        if (isset($this->user) == true) {
            $token['user_id'] = $this->user->id;      // Key needs to be in underscore format for OAuth library
        }
        
        return $token;
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
        $builder->setTable('auth_tokens_access');
        $builder->setCustomRepositoryClass(AccessTokenRepository::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->createField('token', 'string')->length(40)->build();
        $builder->createField('scope', 'string')->length(4000)->nullable(true)->build();
        $builder->addField('expires', 'datetime');

        // Define relationships
        $builder->createManyToOne('client', ClientEntity::class)->addJoinColumn('clientId', 'id')->mappedBy('id')->build();
        $builder->createManyToOne('user', UserEntity::class)->addJoinColumn('userId', 'id')->mappedBy('id')->build();
    }
}