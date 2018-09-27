<?php namespace Tranquility\Data\Entities\Extensions;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection;

// Tranquility class libraries
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class AuditTrailExtension extends AbstractExtension {
    // Entity properties
    protected $transactionId;
    protected $transactionSource;
    protected $updateUserId;
    protected $updateDateTime;
    protected $updateReason;

    // Define the set of fields that are publically accessible
    protected $publicFields = array(
        'transactionId',
        'transactionSource',
        'updateUserId',
        'updateDateTime',
        'updateReason'
    );

    /** Retrieves the set of publically accessible fields for the entity extension object
     * 
     * @return array
     */
    public function getPublicFields() {
        return $this->publicFields;
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
        $builder->createField('transactionId', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('transactionSource', 'string');
        $builder->addField('updateDateTime', 'datetime');
        $builder->addField('updateReason', 'string');
        
        // Add relationships
        $builder->createOneToOne('updateUserId', User::class)->addJoinColumn('updateUserId','id')->build();
    }
}