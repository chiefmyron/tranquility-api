<?php namespace Tranquility\Data\Entities\Business;

// Vendor class libraries
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\System\AuditTransactionEntity;
use Tranquility\Data\Entities\System\TagEntity;
use Tranquility\Data\Repositories\Business\GenericRepository as GenericBusinessRepository;

// Tranquility class libraries
use Tranquility\System\Utility;
use Tranquility\System\Enums\EntityTypeEnum;

abstract class AbstractBusinessEntity extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $type;
    protected $version;
    protected $deleted;

    // Related entities
    protected $transaction;
    protected $tags;

    // Field definitions common to all entity types
    protected static $_commonFieldDefinitions = [
        'id'         => ['type' => 'string',  'visibility' => 'public', 'auditable' => false],
        'version'    => ['type' => 'integer', 'visibility' => 'public', 'auditable' => true],
        'type'       => ['type' => 'string',  'visibility' => 'public', 'auditable' => false],
        'deleted'    => ['type' => 'boolean', 'visibility' => 'public', 'auditable' => false]
    ];

    // Relationship definitions common to all entity types
    protected static $_commonRelationshipDefinitions = [
        'transaction' => ['type' => EntityTypeEnum::AuditTransaction, 'visibility' => 'public', 'collection' => false],
        'tags'        => ['type' => EntityTypeEnum::Tag,              'visibility' => 'public', 'collection' => true]
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
        if (!isset($this->version)) {
            $this->version = 1;
        }
        if (!isset($this->deleted)) {
            $this->deleted = 0;
        }
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
        $builder->setTable('bus_entity');
        $builder->setCustomRepositoryClass(GenericBusinessRepository::class);
        
        // Define inheritence
        $builder->setJoinedTableInheritance();
        $builder->setDiscriminatorColumn('type');
        $builder->addDiscriminatorMapClass(EntityTypeEnum::User, UserEntity::class);
        $builder->addDiscriminatorMapClass(EntityTypeEnum::Person, PersonEntity::class);
        $builder->addDiscriminatorMapClass(EntityTypeEnum::Account, AccountEntity::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->addField('version', 'integer');
        $builder->addField('deleted', 'boolean');
        
        // Define relationships
        $builder->createOneToOne('transaction', AuditTransactionEntity::class)->addJoinColumn('transactionId','id')->build();
        $builder->createManyToMany('tags', TagEntity::class)->setJoinTable('xref_entity_tags')->addInverseJoinColumn('tagId', 'id')->addJoinColumn('entityId', 'id')->inversedBy('entities')->build();
    }
}