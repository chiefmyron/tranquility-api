<?php namespace Tranquility\Services\Business;

// Vendor class libraries
use Carbon\Carbon;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\Business\UserEntity as User;
use Tranquility\Data\Entities\OAuth\ClientEntity as Client;
use Tranquility\Data\Entities\System\AuditTransactionEntity as Transaction;
use Tranquility\Data\Entities\System\AuditTransactionFieldEntity as TransactionField;

// Tranquility class libraries
use Tranquility\Services\AbstractService;
use Tranquility\System\Utility;
use Tranquility\System\Enums\ApplicationErrorCodeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum;

abstract class AbstractBusinessService extends AbstractService {

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
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->create($attributes, $relatedEntities, $transaction);
        return $entity;
    }

    /**
     * Update an existing record for the specified entity
     * 
     * @var  string  $id       Record ID for the entity to update
     * @var  array   $payload  New data to update against the existing record
     * @return  Tranquility\Data\Entities\AbstractEntity
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
        $existingEntity = clone $entity;
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
        $entity->populate($relatedEntities);

        // Data is valid - update the entity
        $transaction = $this->createTransaction($meta, $existingEntity, $entity);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }

    /**
     * Mark an existing entity record as deleted
     * 
     * @var  string  $id       Record ID for the entity to delete
     * @var  array   $payload  Audit trail details to be attached to the deleted record
     * @return \Tranquility\Data\Entities\AbstractEntity
     */
    public function delete(string $id, array $payload) {
        // Force set the deleted flag for the entity
        $payload['data']['attributes']['deleted'] = true;

        // Reuse the update function to process a logical delete
        return $this->update($id, $payload);
    }

    /**
     * Add one or more members to a relationship for an entity
     *
     * @param string  $id
     * @param string  $relatedEntityName
     * @param array   $payload
     * @return \Tranquility\Data\Entities\AbstractEntity
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
     * @return \Tranquility\Data\Entities\AbstractEntity
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
     * @return \Tranquility\Data\Entities\AbstractEntity
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

    /**
     * Generate an Transaction audit trail entity based on metadata in request payload
     *
     * @param array               $meta
     * @param AbstractEntity|null $entityOld
     * @param AbstractEntity|null $entityNew
     * @return Tranquility\Data\Entities\System\Transaction
     */
    protected function createTransaction(array $meta, ?AbstractEntity $entityOld = null, ?AbstractEntity $entityNew = null) {
        // Get audit trail details from authentication token
        $userId = Utility::extractValue($meta, 'user', 0);
        $clientId = Utility::extractValue($meta, 'client', 'invalid_client_id');
        $updateReason = Utility::extractValue($meta, 'updateReason', 'invalid_update_reason');

        // Build audit trail object
        $transactionData = [
            'user' => $this->findUser($userId),
            'client' => $this->findClient($clientId),
            'timestamp' => Carbon::now(),
            'updateReason' => $updateReason
        ];
        $transaction = new Transaction($transactionData);

        // If existing and new versions of the entity being updated have not been provided, no need to generate field-level audit details
        if (is_null($entityOld) && is_null($entityNew)) {
            return $transaction;
        }

        // Retrieve the list of auditable fields
        $entityFieldDefinitions = $entityNew->getPublicFields();
        foreach ($entityFieldDefinitions as $fieldName => $fieldDefinition) {
            $fieldAuditable = Utility::extractValue($fieldDefinition, 'auditable', true, 'bool');
            $fieldDataType = Utility::extractValue($fieldDefinition, 'type', 'string', 'string');
            if ($fieldAuditable == false) {
                continue; // Field is not auditable - skip to next field
            }

            // If value for the field has changed between versions, create a field-level audit record
            if ($entityOld->$fieldName <> $entityNew->$fieldName) {
                $transactionFieldData = [
                    'fieldName' => $fieldName,
                    'dataType' => $fieldDataType,
                    'oldValue' => $entityOld->$fieldName,
                    'newValue' => $entityNew->$fieldName,
                    'entity' => $entityNew,
                    'transaction' => $transaction
                ];
                $transactionField = new TransactionField($transactionFieldData);
                $transaction->addToCollection('fields', $transactionField);
            }
        }

        // Return the populated audit transaction
        return $transaction;
    }

    /**
     * Get the User entity for the specified ID
     * Used when creating an AuditTrail entity
     *
     * @param int $userId
     * @return Tranquility\Data\Entities\BusinessObjects\UserBusinessObject
     */
    protected function findUser($userId) {
        $repository = $this->entityManager->getRepository(User::class);
        $searchOptions = array('id' => $userId);
        return $repository->findOneBy($searchOptions);
    }

    /**
     * Get the OAuth Client entity for the specified ID
     * Used when creating an AuditTrail entity
     *
     * @param int $clientId
     * @return Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject
     */
    protected function findClient($clientId) {
        $repository = $this->entityManager->getRepository(Client::class);
        $searchOptions = array('clientName' => $clientId);
        return $repository->findOneBy($searchOptions);
    }
}