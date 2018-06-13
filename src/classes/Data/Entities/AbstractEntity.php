<?php namespace Tranquility\Data\Entities;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\UserEntity as User;
use Tranquility\Data\EntityExtensions\AuditTrailEntityExtension as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\Data\Repositories\EntityRepository as EntityRepository;

abstract class AbstractEntity {
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
        if (count($data) > 0) {
            // Populate common entity data
            $this->populate($data);

            // Add audit trail details
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
     * Retrieves the value for an entity field
     * 
     * @param string $name  Field name
     * @throws Exception
     * @return mixed
     */
    public function __get($name) {
        $methodName = '_get'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to retrieve value
            return $this->{$methodName}();
        } elseif (in_array($name, $this->getPublicFields())) {
            // Retrieve value directly
            return $this->$name;
        } else {
            throw new \Exception('Cannot get property value - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }

    /**
     * Set the value for an entity field
     * 
     * @param string $name  Field name
     * @param mixed $value  Field value
     * @throws Exception 
     * @return void
     */
    public function __set($name, $value) {
        $methodName = '_set'.ucfirst($name);
        if (method_exists($this, $methodName)) {
            // Use custom function to set value
            $this->{$methodName}($value);
        } elseif (in_array($name, $this->getPublicFields())) {
            // Store value directly
            if ($value !== '') {
                $this->$name = $value;
            }
        } else {
            throw new \Exception('Cannot set property - class "'.get_class($this).'" does not have a property named "'.$name.'"');
        }
    }

    /**
     * Sets values for entity fields, based on the inputs provided
     * 
     * @param mixed $data  May be an array or an instance of an Entity
     * @return Tranquility\Data\Entity
     */
    public function populate($data) {
        if ($data instanceof Entity) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        if (!is_array($data)) {
            throw new \Exception('Initial data must be an array or instance of a Tranquility\Data\Entities\AbstractEntity object');
        }

        // Assign relevant data to the entity fields
        $entityFields = $this->getPublicFields();
        foreach ($entityFields as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Set the audit trail details for an entity
     *
     * @param $auditTrail \Tranquility\Data\BusinessObject\Extensions\AuditTrail
     * @return void
     */
    protected function setAuditTrail($auditTrail) {
        if (!($auditTrail instanceof AuditTrail)) {
            throw new \Exception('Audit trail information must be provided as a Tranquility\Data\EntityExtensions\AuditTrailEntityExtension object');
        }
        
        $this->auditTrail = $auditTrail;
    }
    
    /**
     * Retrieve audit trail details for the entity as an array
     *
     * @return Tranquility\Data\EntityExtensions\AuditTrailEntityExtension
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
        $builder->setCustomRepositoryClass(EntityRepository::class);
        
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