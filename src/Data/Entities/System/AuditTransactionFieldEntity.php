<?php namespace Tranquillity\Data\Entities\System;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquillity\Data\Entities\Business\AbstractBusinessEntity as Entity;

// Tranquillity class libraries
use Tranquillity\System\Enums\EntityTypeEnum;

class AuditTransactionFieldEntity extends AbstractSystemEntity {
    // Entity properties
    protected $entityType;
    protected $fieldName;
    protected $dataType;
    protected $oldValue;
    protected $newValue;

    // Related entities
    protected $entity;
    protected $transaction;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'fieldName'  => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'dataType'   => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'oldValue'   => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'newValue'   => ['type' => 'string', 'visibility' => 'public', 'auditable' => false]
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'entity'      => ['type' => EntityTypeEnum::Entity, 'visibility' => 'public', 'collection' => false],
        'transaction' => ['type' => EntityTypeEnum::AuditTransaction, 'visibility' => 'public', 'collection' => false]
    ];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::AuditTransactionField;
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
        $builder->setTable('sys_audit_txn_fields');
        
        // Define fields
        $builder->createField('fieldName', 'string')->makePrimaryKey()->build();
        $builder->addField('dataType', 'string');
        $builder->addField('oldValue', 'string');
        $builder->addField('newValue', 'string');
        
        // Add relationships
        $builder->createOneToOne('entity', Entity::class)->addJoinColumn('entityId', 'id')->makePrimaryKey()->build();
        $builder->createManyToOne('transaction', AuditTransactionEntity::class)->inversedBy('fields')->addJoinColumn('transactionId', 'id', false)->makePrimaryKey()->build();
    }
}