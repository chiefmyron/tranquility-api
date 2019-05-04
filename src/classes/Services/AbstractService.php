<?php namespace Tranquility\Services;

// Utility libraries
use Carbon\Carbon;
use Valitron\Validator as Validator;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\AbstractEntity as Entity;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;
use Tranquility\System\Enums\FilterOperatorEnum;
use Tranquility\System\Exceptions\InvalidQueryParameterException;

abstract class AbstractService {
    /**
     * Doctrine Entity Manager
     * 
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Array of validation rules used to validate the data entity associated with the resource
     * 
     * @var array
     */
    protected $validationRuleGroups = array();

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $prefix  String to use as database table name prefix
     * @return void
     */
    public function __construct(EntityManagerInterface $em) {
        // Create entity manager for interface to repositories and entities
        $this->entityManager = $em;
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @abstract
     * @return string
     */
    abstract public function getEntityClassname();

    /**
     * Returns the set of publicly available fields for the Entity object associated with this resource
     */
    public function getEntityPublicFields() {
        $entityName = $this->getEntityClassname();
        return $entityName::getPublicFields();
    }

    /**
     * Registers the validation rules that are common to all entities.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Common validation rules for all entities
        // TODO: Validation to prevent user setting values for audit trail fields and deleted flag
        return;
    }

    /**
     * Validate a data array against the defined rules for the resource
     * 
     * @param  array  $data
     * @param  array  $groups  The set of validation groups to use when validating. Runs rules in the 'default' group unless otherwise specified.
     * @return mixed  True if valid, an array of messages if invalid
     */
    public function validate($data, $groups = array('default')) {
        // Create validator instance for the input data
        if ($data instanceof Entity) {
            $data = $data->toArray();
        }
        $validator = new Validator($data);
        
        // Get rules from the specified validation groups
        $rules = array();
        foreach ($groups as $group) {
            if (isset($this->validationRuleGroups[$group])) {
                $rules = array_merge($rules, $this->validationRuleGroups[$group]);
            }
        }

        // Add validation rules
        foreach ($rules as $rule) {
            $params = Utility::extractValue($rule, 'params', array());
            if (is_array($params)) {
                // Handle multiple parameters for a validation rule
                $params = array_merge(array($rule['ruleType'], $rule['field']), $params);
                $validationRule = call_user_func_array(array($validator, 'rule'), $params);
            } else if (is_null($params)) {
                // No parameters
                $validationRule = $validator->rule($rule['ruleType'], $rule['field']);
            }
            
            // Add message to rule
            if (isset($rule['message'])) {
                $validationRule->message($rule['message']);
            }
        }

        // Perform the validation
        $result = $validator->validate();

        // If validation fails, create the error response
        if ($result === false) {
            return $validator->errors();
        }

        // Validation has passed, return true
        return true;
    }

    /**
	 * Perform a text search on the entity
	 *
	 * @param  mixed  $searchTerm             Either a search term string, or an array of search term strings
	 * @param  array  $orderConditions        Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage         If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex       Index of the record to start the result set from. Defaults to zero.
     * @param  bool   $includeDeletedRecords  Includes soft-deleted records if true
	 * @return array
	 */
	public function search($searchTerms, $orderConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0, $includeDeletedRecords = false) {
		// Handle multiple search terms
		if (is_string($searchTerms)) {
			$searchTerms = array($searchTerms);
		}

		// Set up search terms
		$filterConditions = array();
		foreach ($searchTerms as $fieldName) {
			foreach ($searchTerms as $term) {
				$filterConditions[] = array($fieldName, 'LIKE', '%'.$term.'%', 'OR');
			}
        }

		return $this->all($filterConditions, $orderConditions, $resultsPerPage, $startRecordIndex, $includeDeletedRecords);
	}

    /**
	 * Retrieve all entities of this type
	 *
	 * @param  array  $filterConditions       Used to specify additional filters to the set of results
	 * @param  array  $sortingConditions      Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage         If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex       Index of the record to start the result set from. Defaults to zero.
     * @param  bool   $includeDeletedRecords  Includes soft-deleted records if true
	 * @return array
	 */
	public function all($filterConditions = array(), $sortingConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0, $includeDeletedRecords = false) {
        // Get the list of public fields
        $publicFields = $this->getEntityPublicFields();
        $deletedFilterIncluded = false;
        
        // Validate order conditions
        foreach ($sortingConditions as $sortField) {
            if (!in_array($sortField[0], $publicFields)) {
                throw new InvalidQueryParameterException(MessageCodes::ValidationInvalidQueryParameter, sprintf("'%s' is not a sortable field", $sortField[0]), 'sort');
            }
        }

        // Validate filter conditions
        foreach ($filterConditions as $filterField) {
            if ($filterField[0] == 'deleted') {
                $deletedFilterIncluded = true;
            }

            if (!in_array($filterField[0], $publicFields)) {
                throw new InvalidQueryParameterException(MessageCodes::ValidationInvalidQueryParameter, sprintf("'%s' is not a filterable field", $filterField[0]), 'filter');
            }
        }

        // If a 'deleted' filter has not been specified, default to select only records that have not been deleted
        if ($includeDeletedRecords == false && $deletedFilterIncluded === false) {
            $filter = array('deleted', FilterOperatorEnum::Equals, 0);
            $filterConditions[] = $filter;
        }
				
        // Retrieve list of entities from repository
        $results = $this->getRepository()->all($filterConditions, $sortingConditions, $resultsPerPage, $startRecordIndex);
        return $results;
    }

    /**
	 * Find a single entity by ID
	 *
	 * @param  int  $id  Entity ID of the object to retrieve
     * @param  bool $includeDeletedRecords  Includes soft-deleted records if true
	 * @return mixed Returns Tranquility\Data\Entities\AbstractEntity if found, otherwise false
	 */
	public function find($id, $includeDeletedRecords = false) {
        return $this->findOneBy('id', $id, $includeDeletedRecords);
    }
    
    /**
     * Find a single entity by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @param  bool    $includeDeletedRecords  Includes soft-deleted records if true
     * @return mixed Returns Tranquility\Data\Entities\AbstractEntity if found, otherwise false
     */
	public function findOneBy($fieldName, $fieldValue, $includeDeletedRecords = false) {
        // Set search filters. Unless explicitly requested, filter out deleted records.
        $searchOptions = [$fieldName => $fieldValue];
        if ($includeDeletedRecords == false) {
            $searchOptions['deleted'] = false;
        }

        // Retrieve entity from repository
        $entity = $this->getRepository()->findOneBy($searchOptions);

        // If no entity found, generate error response
        if (is_null($entity)) {
            return false;
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
	public function findBy($fieldName, $fieldValue, $includeDeletedRecords = false) {
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

		return $entities;
    }
    
    /**
     * Create a new record for an entity
     * 
     * @var  array  $payload  Data used to create the new entity record
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function create(array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $attributes = Utility::extractValue($data, 'attributes', array());

        // Validate input
        $validationRuleGroups = array('default', 'create');
        $result = $this->validate($attributes, $validationRuleGroups);

        // If there were errors during validation, return them now
        if ($result !== true) {
            return $result; // Array of validation errors
        }

        // Data is valid - create the entity
        $auditTrail = $this->createAuditTrail($meta);
        $entity = $this->getRepository()->create($attributes, $auditTrail);
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

        // Load the existing entity and update the appropriate fields
        $entity = $this->find($id);
        if (!is_object($entity)) {
            // If the entity is not an object, then the specified record cannot be found
            // Return the error collection
            return $entity;
        }

        // Update entity and validate
        $entity->populate($attributes);
        $validationRuleGroups = array('default', 'update');
        $result = $this->validate($entity->toArray(), $validationRuleGroups);

        // If there were errors during validation, return them now
        if ($result !== true) {
            return $result; // Array of validation errors
        }

        // Data is valid - update the entity
        $auditTrail = $this->createAuditTrail($meta);
        $entity = $this->getRepository()->update($entity, $auditTrail);
        return $entity;
    }

    /**
     * Mark an existing entity record as deleted
     * 
     * @var  int    $id       Record ID for the entity to delete
     * @var  array  $payload  Audit trail details to be attached to the deleted record
     * @return boolean
     */
    public function delete(int $id, array $payload) {
        // Force set the deleted flag for the entity
        $payload['data']['attributes']['deleted'] = true;

        // Reuse the update function to process a logical delete
        return $this->update($id, $payload);
    }

    /**
     * Get related entity
     * 
     * @var  int     $id                 Record ID for the entity to delete
     * @var  string  $relatedEntityName  Name of the attribute that refers to the related entity
     * @return boolean
     */
    public function getRelatedEntity(int $id, string $relatedEntityName) {
        // Get the entity
        $entity = $this->find($id);
        if ($entity === false) {
            return $entity;
        }

        // If the related entity is from the audit trail, use the audit trail entity instead
        if ($relatedEntityName == 'updatedByUser') {
            $relatedEntityName = 'user';
            $entity = $entity->audit;
        }

        // Check that the relationship is a public field and has been set
        if (!in_array($relatedEntityName, $entity::getPublicFields())) {
            return false;
        }

        $relatedEntity = $entity->$relatedEntityName;
        if (is_null($relatedEntity)) {
            return false;
        }
        

        // Return related entity
        return $relatedEntity;
    }

    /**
     * Get the Repository associated with the Entity for this resource
     * 
     * @return Tranquility\Data\Repositories\Repository
     */
    protected function getRepository() {
        return $this->entityManager->getRepository($this->getEntityClassname());
    }

    /**
     * Generate an AuditTrail entity based on metadata in request payload
     *
     * @param array $meta
     * @return Tranquility\Data\Entities\System\AuditTrailSystemObject
     */
    protected function createAuditTrail(array $meta) {
        // Get audit trail details from authentication token
        $userId = Utility::extractValue($meta, 'user', 0);
        $clientId = Utility::extractValue($meta, 'client', 'invalid_client_id');
        $updateReason = Utility::extractValue($meta, 'updateReason', 'invalid_update_reason');

        // Build audit trail object
        $auditTrailData = [
            'user' => $this->findUser($userId),
            'client' => $this->findClient($clientId),
            'timestamp' => Carbon::now(),
            'updateReason' => $updateReason
        ];
        $auditTrail = new AuditTrail($auditTrailData);
        return $auditTrail;
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
        $searchOptions = array('clientId' => $clientId);
        return $repository->findOneBy($searchOptions);
    }
}