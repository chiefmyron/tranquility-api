<?php namespace Tranquility\Data\Entities\ReferenceDataObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class LocaleReferenceDataObject extends AbstractReferenceDataObject {
    /** 
     * Retrieves the set of publically accessible fields for the reference data record
     * 
     * @return array
     */
    public function getPublicFields() {
        return $this->referenceDataPublicFields;
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
        $builder->setTable('cd_locales');
    }
}