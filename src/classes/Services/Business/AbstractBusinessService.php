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
use Tranquility\System\Enums\FilterOperatorEnum;

abstract class AbstractBusinessService extends AbstractService {

    /**
	 * Retrieve all entities of this type
	 *
	 * @param  array  $filterConditions       Used to specify additional filters to the set of results
	 * @param  array  $sortingConditions      Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage         If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex       Index of the record to start the result set from. Defaults to zero.
     * @param  bool   $includeDeletedRecords  Includes soft-deleted records if true
	 * @return array|\Tranquility\App\Errors\Helpers\ErrorCollection  Array of entities if successful, or an error collection if validation failed
	 */
	public function all(array $filterConditions = array(), array $sortingConditions = array(), int $resultsPerPage = 0, int $startRecordIndex = 0, bool $includeDeletedRecords = false) {
        // If we are not including deleted records, filter to show only active records
        if ($includeDeletedRecords === false) {
            $filter = ['deleted', FilterOperatorEnum::Equals, 0];
            $filterConditions[] = $filter;
        }

        return parent::all($filterConditions, $sortingConditions, $resultsPerPage, $startRecordIndex);
    }

    /**
     * Find a single entity by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @param  bool    $includeDeletedRecords  Includes soft-deleted records if true
     * @return mixed Returns Tranquility\Data\Entities\AbstractEntity if found, otherwise false
     */
	public function findOneBy(string $fieldName, string $fieldValue, bool $includeDeletedRecords = false) {
        // Set search filters. Unless explicitly requested, filter out deleted records.
        $searchOptions = [$fieldName => $fieldValue];
        if ($includeDeletedRecords == false) {
            $searchOptions['deleted'] = false;
        }

        // Retrieve entity from repository
        $entity = $this->getRepository()->findOneBy($searchOptions);

        // If no entity found, generate error response
        if (is_null($entity) == true) {
            // Invalid entity type supplied for the relationship
            $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound);
            $this->addError($error);
            return $this->getErrors();
        }

        // Return entity
		return $entity;
    }

    /**
     * Find one or more entities by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @param  bool    $includeDeletedRecords  Includes soft-deleted records if true
     * @return array Returns array of Tranquility\Data\Entities\AbstractEntity objects
     */
	public function findBy(string $fieldName, string $fieldValue, bool $includeDeletedRecords = false) {
        // Set search filters. Unless explicitly requested, filter out deleted records.
        $searchOptions = [$fieldName => $fieldValue];
        if ($includeDeletedRecords == false) {
            $searchOptions['deleted'] = false;
        }

        // Retrieve entity collection from repository
        $entities = $this->getRepository()->findBy($searchOptions);

        // If no entity found, return an empty array
        if (count($entities) <= 0) {
            return array();
        }

        // Return entity collection
		return $entities;
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