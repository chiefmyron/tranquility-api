<?php declare(strict_types=1);
namespace Tranquillity\Data\Entities\OAuth;

// Library classes
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Application classes
use Tranquillity\Data\Repositories\OAuth\ClientRepository;
use Tranquillity\System\Utility;
use Tranquillity\System\Enums\EntityTypeEnum;

class ClientEntity extends AbstractHashableFieldOAuthEntity {
    // Entity properties
    protected $id;
    protected $clientName;
    protected $clientSecret;
    protected $redirectUri;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'id'           => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'clientName'   => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'clientSecret' => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'redirectUri'  => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [];

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
        return EntityTypeEnum::OAuthClient;
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
     * Sets the secret for the entity as a hashed value (wrapper required for OAuthServer)
     *
     * @param string $clientSecret
     * @return ClientEntity
     */
    public function setClientSecret($clientSecret) {
        $this->_setClientSecret($clientSecret);
    }

    /**
     * Sets the secret for the entity as a hashed value
     *
     * @param string $clientSecret
     * @return ClientEntity
     */
    protected function _setClientSecret($clientSecret) {
        $this->clientSecret = $this->hashField($clientSecret);
        return $this;
    }

    /**
     * Verify that the supplied secret matches the secret for this client
     *
     * @param string $clientSecret
     * @return bool
     */
    public function verifyClientSecret($clientSecret) {
        return $this->verifyHashedFieldValue($this->clientSecret, $clientSecret);
    }

    /**
     * Cast client to an array for use in the OAuth library
     *
     * @return array
     */
    public function toArray() {
        return [
            'clientId' => $this->clientName,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri
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
        $builder->setTable('auth_clients');
        $builder->setCustomRepositoryClass(ClientRepository::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->addField('clientName', 'string');
        $builder->addField('clientSecret', 'string');
        $builder->createField('redirectUri', 'string')->nullable(true)->build();
    }
}