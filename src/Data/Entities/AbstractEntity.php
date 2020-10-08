<?php namespace Tranquillity\Data\Entities;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractEntity {
    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Initialise related entity collections
        $relationships = $this->getPublicRelationships();
        foreach ($relationships as $name => $relationship) {
            if ($relationship['collection'] === true) {
                $this->$name = new ArrayCollection;
            }
        }
        
        // Populate common entity data
        $this->populate($data);
    }

    /**
     * Sets values for entity fields, based on the inputs provided
     * 
     * @param mixed $data  May be an array or an instance of an Entity
     * @return Tranquillity\Data\Entities\AbstractEntity
     */
    public function populate($data) {
        if ($data instanceof AbstractEntity) {
            $data = $data->toArray();
        } elseif (is_object($data)) {
            $data = (array) $data;
        }
        if (!is_array($data)) {
            throw new \Exception('Initial data must be an array or instance of ' . AbstractEntity::class);
        }

        // Assign relevant data to the entity fields
        $entityFields = $this->getPublicFields();
        $entityRelationships = $this->getPublicRelationships();
        foreach ($data as $field => $value) {
            // Make sure we are only setting values for publicly available fields and relationships
            if (array_key_exists($field, $entityFields) == true) {
                // Assign data to entity field
                $this->$field = $value;
            } elseif (array_key_exists($field, $entityRelationships)) {
                // Assign object(s) to entity relationships
                $relationshipCollection = (bool)$entityRelationships[$field]['collection'];
                if ($relationshipCollection == false) {
                    $this->$field = $value;
                } elseif ($relationshipCollection == true && is_array($value) == false) {
                    $this->$field[] = $value;
                } elseif ($relationshipCollection == true && is_array($value) == true) {
                    $this->$field = array_merge($this->$field, $value);
                }
            }
        }
        
        return $this;
    }

    public function addToCollection($field, $entity) {
        // Check that the field represents a collection
        $entityRelationships = $this->getPublicRelationships();
        if (array_key_exists($field, $entityRelationships) == false || $entityRelationships[$field]['collection'] != true) {
            throw new \Exception('The specified field "' . $field . '" does not represent a collection.');
        }

        // Add the entity to the collection
        $this->$field->add($entity);
        return $this;
    }

    public function removeFromCollection($field, $entity) {
        // Check that the field represents a collection
        $entityRelationships = $this->getPublicRelationships();
        if (array_key_exists($field, $entityRelationships) == false || $entityRelationships[$field]['collection'] != true) {
            throw new \Exception('The specified field "' . $field . '" does not represent a collection.');
        }

        // Add the entity to the collection
        $this->$field->removeElement($entity);
        return $this;
    }

    public function clearCollection($field) {
        // Check that the field represents a collection
        $entityRelationships = $this->getPublicRelationships();
        if (array_key_exists($field, $entityRelationships) == false || $entityRelationships[$field]['collection'] != true) {
            throw new \Exception('The specified field "' . $field . '" does not represent a collection.');
        }

        // Clear the collection
        $this->$field->clear();
        return $this;
    }

    /**
     * Retrieves the value for an entity field
     * 
     * @param string $field  Field name
     * @throws Exception
     * @return mixed
     */
    public function __get($field) {
        $methodName = '_get'.ucfirst($field);
        if (method_exists($this, $methodName)) {
            // Use custom function to retrieve value
            return $this->{$methodName}();
        } elseif (array_key_exists($field, $this->getPublicFields()) || array_key_exists($field, $this->getPublicRelationships())) {
            // Retrieve value directly
            return $this->$field;
        } else {
            throw new \Exception('Cannot get property value - class "'.get_class($this).'" does not have a property named "'.$field.'"');
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
    public function __set($field, $value) {
        $methodName = '_set'.ucfirst($field);
        if (method_exists($this, $methodName)) {
            // Use custom function to set value
            $this->{$methodName}($value);
        } elseif (array_key_exists($field, $this->getPublicFields())) {
            // Store value directly
            if ($value !== '') {
                $this->$field = $value;
            }
        } elseif (array_key_exists($field, $this->getPublicRelationships())) {
            $entityRelationships = $this->getPublicRelationships();

            // Store related entity object
            if ($entityRelationships[$field]['collection'] != true && ($value instanceof AbstractEntity || is_null($value) == true)) {
                $this->$field = $value; 
            } elseif ($entityRelationships[$field]['collection'] == true) {
                throw new \Exception('Cannot set property - "'.$field.'" represents a collection. Use the dedicated "addToCollection", "removeFromCollection" and "clearCollection" functions instead.');
            }
        } else {
            throw new \Exception('Cannot set property - class "'.get_class($this).'" does not have a property named "'.$field.'"');
        }
    }

    /**
     * Checks to see if a value has been set for an entity field
     */
    public function __isset($field) {
        $methodName = '_get'.ucfirst($field);
        if (method_exists($this, $methodName)) {
            // Check to see if the value returned from the custom getter is null
            $value = $this->{$methodName}();
            if (is_null($value)) {
                return false;
            } else {
                return true;
            }
        } elseif (array_key_exists($field, $this->getPublicFields()) || array_key_exists($field, $this->getPublicRelationships())) {
            // Check if value has been set in the entity
            return isset($this->$field);
        } else {
            return false;
        }
    }

    /**
     * Tie the $type property to the defined entity type for the class
     *
     * @return string
     */
    protected function _getType() {
        return static::getEntityType();
    }

    /**
     * Convert the entity to an associative array. Only public fields
     * will be made available in the array
     *
     * @return array
     */
    public function toArray() {
        $data = array();
        $entityFields = $this->getPublicFields();
        foreach ($entityFields as $fieldName => $fieldDefinition) {
            if (isset($this->$fieldName)) {
                $data[$fieldName] = $this->$fieldName;
            }
        }

        return $data;
    }

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     * @abstract
     */
    abstract public static function getEntityType();

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
        $builder->setMappedSuperClass(); // Set as a superclass (no table)
    }
}