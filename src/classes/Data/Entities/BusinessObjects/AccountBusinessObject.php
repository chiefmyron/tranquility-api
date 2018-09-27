<?php namespace Tranquility\Data\Entities;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class AccountBusinessObject extends AbstractBusinessObject {

    // Entity properties
    protected $name;
    
    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);

        // Define table name
        $builder->setTable('entity_accounts');
        $builder->setCustomRepositoryClass('Tranquility\Data\Repositories\EntityRepository');
        
        // Define fields
        $builder->addField('name', 'string');
        
        // Add relationships
        //$builder->createOneToMany('contacts', Contact::class)->mappedBy('account')->orphanRemoval(true)->cascadePersist()->cascadeRemove()->build();
    }
}