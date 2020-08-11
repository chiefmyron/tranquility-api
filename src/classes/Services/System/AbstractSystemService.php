<?php namespace Tranquility\Services\System;

// Framework class libraries
use Tranquility\Services\AbstractService;
use Tranquility\Data\Entities\Business\UserEntity as User;
use Tranquility\Data\Entities\OAuth\ClientEntity as Client;
use Tranquility\Data\Entities\System\AuditTransactionEntity as Transaction;
use Tranquility\System\Enums\ApplicationErrorCodeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum;
use Tranquility\System\Utility as Utility;

abstract class AbstractSystemService extends AbstractService {

    /**
	 * Find a single entity by ID
	 *
	 * @param  int  $id  Entity ID of the object to retrieve
     * @param  bool $includeDeletedRecords  Includes soft-deleted records if true
	 * @return mixed Returns Tranquility\Data\Entities\AbstractEntity if found, otherwise false
	 */
	public function find($id, $includeDeletedRecords = false) {
        return $this->findOneBy('externalId', $id, $includeDeletedRecords);
	}
	
	    /**
     * Create a new record for an entity
     * 
     * @var  array  $payload  Data used to create the new entity record
     * @return \Tranquility\Data\Entities\AbstractEntity
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
     * @var  int         $id       Record ID for the entity to update
     * @var  array       $payload  New data to update against the existing record
     * @return  Tranquility\Data\Entities\AbstractEntity
     */
    public function update(int $id, array $payload) {
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
     * @var  int    $id       Record ID for the entity to delete
     * @var  array  $payload  Audit trail details to be attached to the deleted record
     * @return \Tranquility\Data\Entities\AbstractEntity
     */
    public function delete(int $id, array $payload) {
        // Force set the deleted flag for the entity
        $payload['data']['attributes']['deleted'] = true;

        // Reuse the update function to process a logical delete
        return $this->update($id, $payload);
	}
	
	/**
     * Add one or more members to a relationship for an entity
     *
     * @param integer $id
     * @param string  $relatedEntityName
     * @param array   $payload
     * @return \Tranquility\Data\Entities\AbstractEntity
     */
    public function addRelationshipMembers(int $id, string $relatedEntityName, array $payload) {
		
	}
}