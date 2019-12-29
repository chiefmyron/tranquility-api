<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository as BusinessObjectRepository;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

abstract class AbstractBusinessObject extends AbstractEntity {
    // Entity properties
    protected $id;
    protected $version;
    protected $subType;
    protected $deleted;
    protected $locks;

    // Related entities
    protected $transaction;
    protected $tags;

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
        'transaction' => ['entityType' => EntityTypeEnum::Transaction, 'relationshipType' => EntityRelationshipTypeEnum::Single],
        'tags' => ['entityType' => EntityTypeEnum::Tag, 'relationshipType' => EntityRelationshipTypeEnum::Collection]
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

        // Initialise related entity collections
        $relationships = $this->getPublicRelationships();
        foreach ($relationships as $name => $relationship) {
            if ($relationship['relationshipType'] == EntityRelationshipTypeEnum::Collection) {
                $this->$name = new ArrayCollection;
            }
        }
    }

    /**
     * Returns the name of the class used to model the historical records for this business object
     *
     * @return string
     */
    public static function getHistoricalEntityClass() {
        $entityType = static::getEntityType();
        return EntityTypeEnum::getHistoricalEntityClassname($entityType);
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
        $builder->setTable('entity');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define inheritence
        $builder->setJoinedTableInheritance();
        $builder->setDiscriminatorColumn('type');
        $builder->addDiscriminatorMapClass(EntityTypeEnum::User, EntityTypeEnum::getEntityClassname(EntityTypeEnum::User));
        $builder->addDiscriminatorMapClass(EntityTypeEnum::Person, EntityTypeEnum::getEntityClassname(EntityTypeEnum::Person));

        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Account, Account::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::Address, Address::class);
        //$builder->addDiscriminatorMapClass(EntityTypeEnum::AddressPhysical, AddressPhysical::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('version', 'integer');
        $builder->addField('deleted', 'boolean');
        
        // Add relationships
        $builder->createOneToOne('transaction', EntityTypeEnum::getEntityClassname(EntityTypeEnum::Transaction))->addJoinColumn('transactionId','id')->build();
        $builder->createManyToMany('tags', EntityTypeEnum::getEntityClassname(EntityTypeEnum::Tag))->setJoinTable('entity_tags_xref')->addInverseJoinColumn('tagId', 'id')->addJoinColumn('entityId', 'id')->inversedBy('entities')->build();
    }
}