<?php namespace Tranquility\Data\Entities\ReferenceDataObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class TimezoneReferenceDataObject extends AbstractReferenceDataObject {
    // Entity properties
    protected $daylightSavings;

    // Define the set of fields that are publicly accessible
    protected $publicFields = array(
        'daylightSavings'
    );

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$referenceDataPublicFields, self::$publicFields);
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
        $builder->setTable('cd_timezones');
        
        // Define fields
        $builder->addField('daylightSavings', 'boolean');
    }
}