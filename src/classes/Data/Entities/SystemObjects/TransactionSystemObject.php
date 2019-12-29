<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

// Tranquility class libraries
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class TransactionSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $id;
    protected $client;
    protected $user;
    protected $timestamp;
    protected $updateReason;

    // Define the set of fields that are publicly accessible
    protected static $publicFields = array(
        'id',
        'type',
        'timestamp',
        'updateReason'
    );

    // Define the set of related entities or entity collections that are publicly available
    protected static $publicRelationships = array(
        'user' => ['entityType' => EntityTypeEnum::User, 'relationshipType' => EntityRelationshipTypeEnum::Single],
        'client' => ['entityType' => EntityTypeEnum::OAuthClient, 'relationshipType' => EntityRelationshipTypeEnum::Single]
    );

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::Transaction;
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
        $builder->setTable('sys_trans_audit');
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('timestamp', 'datetime');
        $builder->addField('updateReason', 'string');
        
        // Add relationships
        $builder->createOneToOne('user', User::class)->addJoinColumn('user', 'id')->build();
        $builder->createOneToOne('client', Client::class)->addJoinColumn('client', 'id')->build();
    }
}