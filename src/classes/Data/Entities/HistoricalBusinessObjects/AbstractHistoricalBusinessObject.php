<?php namespace Tranquility\Data\Entities\HistoricalBusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Entities\HistoricalBusinessObjects\UserHistoricalBusinessObject as HistoricalUser;
use Tranquility\Data\Entities\HistoricalBusinessObjects\PersonHistoricalBusinessObject as HistoricalPerson;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject as Transaction;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

abstract class AbstractHistoricalBusinessObject extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $version;
    protected $type;
    protected $subType;
    protected $deleted;
    protected $locks;

    // Related extension data objects
    protected $transaction;

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
        'transaction' => ['entityType' => EntityTypeEnum::Transaction, 'relationshipType' => EntityRelationshipTypeEnum::Single]
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
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        // Define table name (but use default Doctrine repository)
        $builder->setTable('history_entity');
        
        // Define inheritence
        $builder->setJoinedTableInheritance();
        $builder->setDiscriminatorColumn('type');
        $builder->addDiscriminatorMapClass(EntityTypeEnum::User, HistoricalUser::class);
        $builder->addDiscriminatorMapClass(EntityTypeEnum::Person, HistoricalPerson::class);

        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Account, Account::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Address, Address::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::AddressPhysical, AddressPhysical::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->build();
        $builder->createField('version', 'integer')->isPrimaryKey()->build();
        $builder->addField('deleted', 'boolean');
        
        // Add relationships
        $builder->createOneToOne('transaction', Transaction::class)->addJoinColumn('transactionId','id')->build();
    }
}