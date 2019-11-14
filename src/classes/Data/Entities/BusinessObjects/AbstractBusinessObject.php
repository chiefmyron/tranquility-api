<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\BusinessObjects\PersonBusinessObject as Person;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject as Transaction;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
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
    protected $transaction;
    protected $tagCollection;

    // Define the set of fields that are publicly accessible
    protected static $entityPublicFields = array(
        'id',
        'version',
        'type',
        'subType',
        'deleted',
        'locks'
    );

    // Define the set of related entities or entity collections that are publicly available
    protected static $entityPublicRelationships = array(
        "transaction" => ["entityType" => EntityTypeEnum::Transaction, "relationshipType" => "single"]
        //["name" => "tags", "entityType" => EntityTypeEnum::Tag, "relationshipType" => "collection"]
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

        // Ensure version and deleted properties are initialised
        if (!isset($this->version)) {
            $this->version = 1;
        }
        if (!isset($this->deleted)) {
            $this->deleted = 0;
        }
    }

    /**
     * Set the audit trail transaction details for an entity
     *
     * @param Tranquility\Data\Entities\SystemObjects\TransactionSystemObject $transaction
     * @return void
     */
    protected function _setTransaction($transaction) {
        if (!($transaction instanceof Transaction)) {
            throw new \Exception('Audit trail transaction information must be provided as a ' . Transaction::class . ' object');
        }
        
        $this->transaction = $transaction;
    }
    
    /**
     * Retrieve audit trail details for the entity as an array
     *
     * @return Tranquility\Data\Entities\SystemObjects\TransactionSystemObject
     */
    protected function _getTransaction() {
        return $this->transaction;
    }

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public static function getPublicFields();

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public static function getPublicRelationships();

    /**
     * Returns the name of the class used to model the historical records for this business object
     *
     * @return string
     */
    abstract public static function getHistoricalEntityClass();

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
        $builder->addDiscriminatorMapClass(EntityTypeEnum::Person, Person::class);

        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Account, Account::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Address, Address::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::AddressPhysical, AddressPhysical::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('version', 'integer');
        $builder->addField('deleted', 'boolean');
        
        // Add relationships
        $builder->createOneToOne('transaction', Transaction::class)->addJoinColumn('transactionId','id')->build();
        //$builder->createManyToMany('tags', Tag::class)->inversedBy('entities')->setJoinTable('entity_tags_xref')->addJoinColumn('entityId', 'id')->addInverseJoinColumn('tagId', 'id')->build();
        //$builder->createManyToMany('relatedEntities', BusinessObject::class)->setJoinTable('entity_entity_xref')->addJoinColumn('parentId', 'id')->addInverseJoinColumn('childId', 'id')->build();
    }
}