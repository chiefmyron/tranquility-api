<?php declare(strict_types=1);
namespace Tranquillity\Data\Entities\OAuth;

// Library classes
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Application classes
use Tranquillity\Data\Repositories\OAuth\ScopeRepository;
use Tranquillity\System\Utility;
use Tranquillity\System\Enums\EntityTypeEnum as EntityTypeEnum;

class ScopeEntity extends AbstractOAuthEntity {
    // Entity properties
    protected $id;
    protected $scope;
    protected $isDefault;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'id'        => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'scope'     => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'isDefault' => ['type' => 'boolean', 'visibility' => 'public', 'auditable' => false],
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
        return EntityTypeEnum::OAuthScope;
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
     * Cast scope to an array for use in the OAuth library
     *
     * @return array
     */
    public function toArray() {
        return [
            'scope' => $this->scope,
            'isDefault' => $this->isDefault
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
        $builder->setTable('auth_scopes');
        $builder->setCustomRepositoryClass(ScopeRepository::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->addField('scope', 'string');
        $builder->addField('isDefault', 'boolean');
    }
}