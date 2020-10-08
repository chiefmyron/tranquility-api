<?php namespace Tranquillity\Services\System;

// Entity classes
use Tranquillity\Data\Entities\AbstractEntity;

// Tranquillity class libraries
use Tranquillity\Services\AbstractService;
use Tranquillity\System\Enums\ApplicationErrorCodeEnum;
use Tranquillity\System\Utility as Utility;

abstract class AbstractSystemService extends AbstractService {

    /**
	 * Find a single entity by ID
	 *
	 * @param  int  $id  Entity ID of the object to retrieve
     * @param  bool $includeDeletedRecords  Includes soft-deleted records if true
	 * @return mixed Returns Tranquillity\Data\Entities\AbstractEntity if found, otherwise false
	 */
	public function find($id, $includeDeletedRecords = false) {
        return $this->findOneBy('externalId', $id, $includeDeletedRecords);
	}
	
	    /**
     * Create a new record for an entity
     * 
     * @var  array  $payload  Data used to create the new entity record
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function create(array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $attributes = Utility::extractValue($data, 'attributes', array());
        $relationships = Utility::extractValue($data, 'relationships', array());

        // Validate input attributes and related entities
        $validationRuleGroups = array('default', 'create');
        $this->validateAttributes($attributes, $validationRuleGroups);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while validating attributes and relationships
        }

        // Load related entities
        $relatedEntities = $this->hydrateResourceLinkage($relationships);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while loading related entities
        }

        // Data is valid - create the entity
        $entity = $this->getRepository()->create($attributes, $relatedEntities);
        return $entity;
    }

    /**
     * Update an existing record for the specified entity
     * 
     * @var  string  $id       Record ID for the entity to update
     * @var  array   $payload  New data to update against the existing record
     * @return  Tranquillity\Data\Entities\AbstractEntity
     */
    public function update(string $id, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $attributes = Utility::extractValue($data, 'attributes', array());
        $relationships = Utility::extractValue($data, 'relationships', array());

        // Load the existing entity and update the appropriate fields
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // Validate updated entity attributes and related entities
        $entity->populate($attributes);
        $validationRuleGroups = array('default', 'update');
        $this->validateAttributes($entity, $validationRuleGroups);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while validating attributes and relationships
        }

        // Load related entities
        $relatedEntities = $this->hydrateResourceLinkage($relationships);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while loading related entities
        }

        // Update entity with new attribute and relationship values
        $entity->populate($relatedEntities);

        // Data is valid - update the entity
        $entity = $this->getRepository()->update($entity);
        return $entity;
    }

    /**
     * Mark an existing entity record as deleted
     * 
     * @var  string  $id       Record ID for the entity to delete
     * @var  array   $payload  Audit trail details to be attached to the deleted record
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function delete(string $id, array $payload) {
        // Force set the deleted flag for the entity
        $payload['data']['attributes']['deleted'] = true;

        // Reuse the update function to process a logical delete
        return $this->update($id, $payload);
    }
    
    /**
     * Generate an Transaction audit trail entity based on metadata in request payload
     *
     * @param array               $meta
     * @param AbstractEntity|null $entityOld
     * @param AbstractEntity|null $entityNew
     * @return Tranquillity\Data\Entities\System\Transaction
     */
    protected function createTransaction(array $meta, ?AbstractEntity $entityOld = null, ?AbstractEntity $entityNew = null) {
        return null; // Transactions are not attached to system entities
    }
	
    /**
     * Add one or more members to a relationship for an entity
     *
     * @param string  $id
     * @param string  $relatedEntityName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function addRelationshipMembers(string $id, string $relatedEntityName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $relationships = [$relatedEntityName => $data];

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relatedEntityName]['collection'];

        // Validate relationship details
        if (is_null($data) == true || count($data) <= 0) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When adding entities to a relationship, at least one entity must be provided in the request.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (array_key_exists($relatedEntityName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relatedEntityName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == true) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidType, "The relationship named '".$relatedEntityName."' is for a single entity - cannot accept a collection of entities.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Load the existing entity
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // Load related entities
        $relatedEntities = $this->hydrateResourceLinkage($relationships);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while loading related entities
        }

        // If related entity does not already exist in the collection, add it now
        foreach ($relatedEntities[$relatedEntityName] as $relatedEntity) {
            if ($entity->$relatedEntityName->contains($relatedEntity) == false) {
                $entity->addToCollection($relatedEntityName, $relatedEntity);
            }
        }

        // Data is valid - update the entity
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }

    /**
     * Update members in the relationship for an entity. This is a REPLACEMENT of any existing members
     * in the relationship. Can also be used to clear the relationship when data is 'null' (for one-to-one relationships)
     * or an empty array (for collection relationships).
     *
     * @param string  $id
     * @param string  $relatedEntityName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function updateRelationshipMembers(string $id, string $relatedEntityName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data');
        $relationships = [$relatedEntityName => $data];

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relatedEntityName]['collection'];

        // Validate relationship details
        if (array_key_exists($relatedEntityName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relatedEntityName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == true && is_null($data) == true) {
            // Cannot clear a collection by supplying 'null'
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "The relationship named '".$relatedEntityName."' is for a collection - 'data' member must contain either an array of resource identifiers (or an empty array to clear the relationship).");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == false && is_array($data) == true && count($data) != 1) {
            // Cannot clear a single entity relationship by supplying an array
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "The relationship named '".$relatedEntityName."' is for a single entity - 'data' member must contain either a single resource identifier (or null to clear the relationship).");
            $this->addError($error);
            return $this->getErrors();
        }

        // Load the existing entity
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // If we are working with a collection relationship, always clear the collection (it is either being cleared or replaced)
        if ($relationshipCollection == true) {
            $entity->clearCollection($relatedEntityName);
        }

        // Updating the relationship depends on what data is provided and the type of relationship
        if ($relationshipCollection == false && is_null($data) == true) {
            // Clear the relationship
            $entity->$relatedEntityName = null;
        } else {
            // Get details of related entities
            $relatedEntities = $this->hydrateResourceLinkage($relationships);
            if ($this->hasErrors() == true) {
                return $this->getErrors();  // Errors encountered while loading related entities
            }

            // Update relationship with entity details
            foreach ($relatedEntities[$relatedEntityName] as $relatedEntity) {
                if ($relationshipCollection == true) {
                    // Add to collection
                    $entity->addToCollection($relatedEntityName, $relatedEntity);
                } else {
                    // Replace existing relationship member
                    $entity->$relatedEntityName = $relatedEntity;
                }
            }
        }

        // Data is valid - update the entity
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }

    /**
     * Delete specified members in the relationship for an entity. Can only be used for deleting members of a 
     * collection relationship.
     *
     * @param string  $id
     * @param string  $relatedEntityName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function deleteRelationshipMembers(string $id, string $relatedEntityName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $relationships = [$relatedEntityName => $data];

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relatedEntityName]['collection'];

        // Validate relationship details
        if (is_null($data) == true || count($data) <= 0) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When deleting entities from a relationship, at least one entity must be provided in the request.");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == true) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidType, "The relationship named '".$relatedEntityName."' is for a single entity - cannot accept a collection of entities.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Load the existing entity
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // Load related entities
        $relatedEntities = $this->hydrateResourceLinkage($relationships);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while loading related entities
        }

        // If related entity does not already exist in the collection, add it now
        foreach ($relatedEntities[$relatedEntityName] as $relatedEntity) {
            if ($entity->$relatedEntityName->contains($relatedEntity) == false) {
                $entity->removeFromCollection($relatedEntityName, $relatedEntity);
            }
        }

        // Data is valid - update the entity
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }
}