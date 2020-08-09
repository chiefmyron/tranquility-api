<?php namespace Tranquility\Data\Entities\Reference;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class TimezoneReferenceDataEntity extends AbstractReferenceDataEntity {
    // Entity properties
    protected $daylightSavings;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'daylightSavings' => ['type' => 'string', 'visibility' => 'public', 'auditable' => false]
    ];

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$_commonFieldDefinitions, self::$_entityFieldDefinitions);
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
        $builder->setTable('ref_timezones');
        
        // Define fields
        $builder->addField('daylightSavings', 'boolean');
    }
}