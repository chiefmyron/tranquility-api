<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity as Entity;
use Tranquility\Data\Repositories\SystemObjectRepository as SystemObjectRepository;
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

class TagSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $id;
    protected $text;

    // Related entities
    protected $entities;

    // Define the set of fields that are publicly accessible
    protected static $publicFields = array(
        'id',
        'type',
        'text'
    );

    // Define the set of related entities or entity collections that are publicly available
    protected static $publicRelationships = array(
        'entities' => ['entityType' => EntityTypeEnum::Entity, 'relationshipType' => EntityRelationshipTypeEnum::Collection]
    );

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::Tag;
    }

    /** Retrieves the set of publicly accessible fields for the entity extension object
     * 
     * @return array
     */
    public static function getPublicFields() {
        return self::$publicFields;
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return self::$publicRelationships;
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
        $builder->setTable('ext_tags');
        $builder->setCustomRepositoryClass(SystemObjectRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('text', 'string');

        // Add relationships
        $builder->createManyToMany('entities', Entity::class)->setJoinTable('entity_tags_xref')->mappedBy('tags')->build();
    }
}