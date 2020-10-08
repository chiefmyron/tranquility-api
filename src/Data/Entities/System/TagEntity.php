<?php namespace Tranquillity\Data\Entities\System;

// Vendor class libraries
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquillity\Data\Entities\Business\AbstractBusinessEntity;
use Tranquillity\Data\Repositories\System\GenericRepository;

// Tranquillity class libraries
use Tranquillity\System\Utility;
use Tranquillity\System\Enums\EntityTypeEnum;

class TagEntity extends AbstractSystemEntity {
    // Entity properties
    protected $id;
    protected $label;

    // Related entities
    protected $entities;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'id'    => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'label' => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'entities' => ['type' => EntityTypeEnum::Entity, 'visibility' => 'public', 'collection' => true]
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
        return EntityTypeEnum::Tag;
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
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        
        // Define table name
        $builder->setTable('sys_tags');
        $builder->setCustomRepositoryClass(GenericRepository::class);
        
        // Define fields
        $builder->createField('id', UuidBinaryOrderedTimeType::NAME)->makePrimaryKey()->build();
        $builder->addField('label', 'string');

        // Add relationships
        $builder->createManyToMany('entities', AbstractBusinessEntity::class)->mappedBy('tags')->build();
    }
}