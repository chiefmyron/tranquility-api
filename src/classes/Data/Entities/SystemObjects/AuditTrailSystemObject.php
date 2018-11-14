<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection;

// Tranquility class libraries
use Tranquility\Data\Entities\OAuth\ClientOAuth as Client;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class AuditTrailSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $transactionId;
    protected $client;
    protected $user;
    protected $timestamp;
    protected $updateReason;

    // Define the set of fields that are publically accessible
    protected $publicFields = array(
        'transactionId',
        'user',
        'client',
        'timestamp',
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
        $builder->addField('timestamp', 'datetime');
        $builder->addField('updateReason', 'string');
        
        // Add relationships
        $builder->createOneToOne('user', User::class)->addJoinColumn('user', 'id')->build();
        $builder->createOneToOne('client', Client::class)->addJoinColumn('client', 'id')->build();
    }
}