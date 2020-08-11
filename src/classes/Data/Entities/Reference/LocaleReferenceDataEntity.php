<?php namespace Tranquility\Data\Entities\Reference;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class LocaleReferenceDataEntity extends AbstractReferenceDataEntity {

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        // There are no additional fields for this reference data collection
        return self::$_commonFieldDefinitions;
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
        $builder->setTable('ref_locales');
    }
}