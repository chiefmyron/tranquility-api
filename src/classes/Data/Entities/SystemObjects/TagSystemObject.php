<?php namespace Tranquility\Data\Entities\SystemObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity as Entity;
use Tranquility\Data\Repositories\SystemObjectRepository as SystemObjectRepository;
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;

class TagSystemObject extends AbstractSystemObject {
    // Entity properties
    protected $id;
    protected $text;

    // Related entities
    protected $entities;

    // Define the set of fields that are publically accessible
    protected $entityPublicFields = array(
        'id',
        'text'
    );

    /**
     * Create a new instance of the Tag
     * 
     * @return void
     */
    public function __construct() {
        // Initialise collection for related entities
        $this->entities = new ArrayCollection();
    }

    /**
     * Retreive a collection of business object entities associated with this tag
     *
     * @return array
     */
    public function getRelatedEntities() {
        return $this->entities->toArray();
    }
    
    /**
     * Remove an entity from the collection of related entities for the tag
     *
     * @param Tranquility\Data\Entities\AbstractEntity $entity   Existing entity to remove from the collection of related entities
     * @return Tranquility\Data\Entities\Extensions\TagEntityExtension
     */
    public function removeRelatedEntity(Entity $entity) {
        if ($this->entities->contains($entity) === false) {
            return $this;
        }
        $this->entities->removeElement($entity);
        return $this;
    }
    
    /**
     * Add a new entity to the collection of related entities for the tag
     *
     * @param Tranquility\Data\Entities\AbstractEntity $entity   New entity to associated with the tag
     * @return Tranquility\Data\Entities\Extensions\TagEntityExtension
     */
    public function addRelatedEntity(Entity $entity) {
        if ($this->entities->contains($entity) === true) {
            return $this;
        }
        
        $this->entities->add($entity);
        return $this;
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
        $builder->setTable('ext_tags');
        $builder->setCustomRepositoryClass(SystemObjectRepository::class);
        
        // Define fields
        $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
        $builder->addField('text', 'string');
        
        // Add relationships
        $builder->createManyToMany('entities', Entity::class)->mappedBy('tags')->build();
    }
}