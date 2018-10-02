<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\Extensions\AuditTrailExtension as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository as BusinessObjectRepository;

abstract class AbstractBusinessObject extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $version;
    protected $type;
    protected $subType;
    protected $deleted;
    protected $locks;

    // Related extension data objects
    protected $auditTrail;
    protected $tagCollection;

    // Define the set of fields that are publically accessible
    protected $entityPublicFields = array(
        'id',
        'version',
        'type',
        'subType',
        'deleted',
        'locks'
    );

    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Set values for valid properties
        parent::__construct($data, $options);

        // Add audit trail details
        if (count($data) > 0) {
            $auditTrail = new AuditTrail($data);
            $this->setAuditTrail($auditTrail);
        }
        
        // Ensure version and deleted properties are initialised
        if (!isset($this->version)) {
            $this->version = 1;
        }
        if (!isset($this->deleted)) {
            $this->deleted = 0;
        }
    }

    /**
     * Set the audit trail details for an entity
     *
     * @param $auditTrail \Tranquility\Data\Entities\Extensions\AuditTrailExtension
     * @return void
     */
    public function setAuditTrail($auditTrail) {
        if (!($auditTrail instanceof AuditTrail)) {
            throw new \Exception('Audit trail information must be provided as a Tranquility\Data\Entities\Extensions\AuditTrailExtension object');
        }
        
        $this->auditTrail = $auditTrail;
    }
    
    /**
     * Retrieve audit trail details for the entity as an array
     *
     * @return Tranquility\Data\Entities\Extensions\AuditTrailExtension
     */
    protected function getAuditTrail() {
        return $this->auditTrail;
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public function getPublicFields();

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        // Define table name
        $builder->setTable('entity');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define inheritence
        $builder->setJoinedTableInheritance();
        $builder->setDiscriminatorColumn('type');
        $builder->addDiscriminatorMapClass(EntityTypeEnum::User, User::class);

        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Person, Person::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Account, Account::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Address, Address::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::AddressPhysical, AddressPhysical::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('version', 'integer');
        $builder->addField('deleted', 'boolean');
        
        // Add relationships
        $builder->createOneToOne('auditTrail', AuditTrail::class)->addJoinColumn('transactionId','transactionId')->build();
        //$builder->createManyToMany('tags', Tag::class)->inversedBy('entities')->setJoinTable('entity_tags_xref')->addJoinColumn('entityId', 'id')->addInverseJoinColumn('tagId', 'id')->build();
        //$builder->createManyToMany('relatedEntities', BusinessObject::class)->setJoinTable('entity_entity_xref')->addJoinColumn('parentId', 'id')->addInverseJoinColumn('childId', 'id')->build();
    }
}