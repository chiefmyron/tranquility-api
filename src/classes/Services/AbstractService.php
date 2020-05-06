<?php namespace Tranquility\Services;

// Utility libraries
use Carbon\Carbon;
use Valitron\Validator as Validator;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;
use Tranquility\App\Errors\AbstractError;
use Tranquility\App\Errors\EntityNotFoundError;

// Tranquility data entities
use Tranquility\Data\Entities\AbstractEntity as Entity;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\OAuthClientSystemObject as Client;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject as Transaction;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;
use Tranquility\System\Enums\FilterOperatorEnum;
use Tranquility\System\Enums\ApplicationErrorCodeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum;

// Tranquility service error handling
use Tranquility\App\Errors\Helpers\ErrorCollection;
use Tranquility\System\Enums\EntityTypeEnum;

abstract class AbstractService {
    /**
     * Doctrine Entity Manager
     * 
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Validation engine
     * 
     * @var \Valitron\Validator
     */
    protected $validator;

    /**
     * Class name of the data entity primarily related to the service
     *
     * @var \Tranquility\Data\Entities\AbstractEntity
     */
    protected $entityClassname;

    /**
     * Array of validation rules used to validate the data entity associated with the resource
     * 
     * @var array
     */
    protected $validationRuleGroups = array();

    /**
     * A collection of validation or business logic errors generated during service call execution
     *
     * @var \Tranquility\App\Errors\Helpers\ErrorCollection
     */
    protected $errors;

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $em         ORM entity manager
     * @param  \Valitron\Validator                   $validator  Validation engine 
     * @return void
     */
    public function __construct(EntityManagerInterface $em, Validator $validator) {
        // Create entity manager for interface to repositories and entities
        $this->entityManager = $em;
        $this->validator = $validator;
        $this->errors = new ErrorCollection();

        // Register validation rules for the service
        $this->registerValidationRules();
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @return string
     */
    public function getEntityClassname() {
        return $this->entityClassname;
    }

    /**
     * Returns the set of publicly available fields for the Entity object associated with this resource
     * 
     * @return array
     */
    public function getEntityPublicFields() {
        $entityName = $this->getEntityClassname();
        return $entityName::getPublicFields();
    }

    /**
     * Returns an array describing the related entities or entity collections for the entity
     *
     * @return array
     */
    public function getEntityPublicRelationships() {
        $entityName = $this->getEntityClassname();
        return $entityName::getPublicRelationships();
    }

    /**
     * Registers the validation rules that are common to all entities.
     * 
     * @abstract
     * @return void
     */
    abstract public function registerValidationRules();

    /**
     * Validate a data array against the defined rules for the resource
     * 
     * @param  array  $data
     * @param  array  $groups  The set of validation groups to use when validating. Runs rules in the 'default' group unless otherwise specified.
     * @return mixed  True if valid, an array of messages if invalid
     */
    public function validateAttributes($data, $groups = array('default')) {
        // Create validator instance for the input data
        if ($data instanceof Entity) {
            $data = $data->toArray();
        }
        $validator = $this->validator->withData($data);

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
            // Add error details
            foreach ($validator->errors() as $field => $messages) {
                foreach ($messages as $code) {
                    $error = $this->createError($code);
                    $error->addErrorSource('pointer', '/data/attributes/'.$field);
                    $this->addError($error);
                }
            }

            return false;
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
	 * @return array|\Tranquility\App\Errors\Helpers\ErrorCollection  Array of entities if successful, or an error collection if validation failed
	 */
	public function all($filterConditions = array(), $sortingConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0, $includeDeletedRecords = false) {
        // Get the list of public fields
        $publicFields = $this->getEntityPublicFields();
        $deletedFilterIncluded = false;
        
        // Validate order conditions
        foreach ($sortingConditions as $sortField) {
            if (!in_array($sortField[0], $publicFields)) {
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationInvalidQueryParameter, sprintf("'%s' is not a sortable field", $sortField[0]));
                $error->addErrorSource('parameter', 'sort');
                $this->errors->addError($error);
            }
        }

        // Validate filter conditions
        foreach ($filterConditions as $filterField) {
            if ($filterField[0] == 'deleted') {
                $deletedFilterIncluded = true;
            }

            if (!in_array($filterField[0], $publicFields)) {
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationInvalidQueryParameter, sprintf("'%s' is not a filterable field", $filterField[0]));
                $error->addErrorSource('parameter', 'filter');
                $this->errors->addError($error);
            }
        }

        // If validation failed, return the error collection
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while validating attributes and relationships
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
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
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
     * Get related entity
     * 
     * @var  int     $id                 ID for the parent entity
     * @var  string  $relatedEntityName  Name of the attribute that refers to the related entity
     * @return \Tranquility\Data\Entities\AbstractEntity
     */
    public function getRelatedEntity(int $id, string $relatedEntityName) {
        // Check that the relationship is a public field
        if (!array_key_exists($relatedEntityName, $this->getEntityPublicRelationships())) {
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotFound, "'".$relatedEntityName."' is not a valid relationship for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Get the entity
        $entity = $this->find($id);
        if ($entity === false || is_null($entity->$relatedEntityName)) {
            // Invalid entity type supplied for the relationship
            $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Related resource named '".$relatedEntityName."' was not found for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Return related entity
        return $entity->$relatedEntityName;
    }


    public function addRelationshipMembers(int $id, string $relatedEntityName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());
        $relationships = [$relatedEntityName => $data];

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipType = $relationshipDetail[$relatedEntityName]['relationshipType'];
        $relationshipEntityType = $relationshipDetail[$relatedEntityName]['entityType'];

        // Validate relationship details
        if (array_key_exists($relatedEntityName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relatedEntityName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipType == EntityRelationshipTypeEnum::Single) {
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
                $entity->$relatedEntityName[] = $relatedEntity;
            }
        }

        // Data is valid - create the entity
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }

    /**
     * Hydrate a resource linkage document with related entity records
     *
     * @param array $relationships
     * @param boolean $includeDeletedRecords
     * @return array
     */
    protected function hydrateResourceLinkage(array $relationships, bool $includeDeletedRecords = false) {
        // Get allowed relationships for the entity
        $entityRelationshipDetails = $this->getEntityPublicRelationships();
        
        // Check each relationship name and data
        $hydratedLinkage = array();
        foreach ($relationships as $name => $values) {
            $validationData = $values['data'];

            // Check relationship name is valid
            if (array_key_exists($name, $entityRelationshipDetails) == false) {
                // Invalid relationship error
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$name."' for this entity.");
                $error->addErrorSource('pointer', '/data/relationships/'.$name);
                $this->addError($error);
                continue;
            }

            // If this is a 'to-one' relationship, check that data is only a single relationship object
            $relationshipType = $entityRelationshipDetails[$name]['relationshipType'];
            $relationshipEntityType = $entityRelationshipDetails[$name]['entityType'];
            if ($relationshipType == EntityRelationshipTypeEnum::Single) {
                // Put this into a collection to simplify the remaining validation logic
                $validationData = array($validationData);
            } 

            // Get the repository for this relationship
            $repository = $this->entityManager->getRepository(EntityTypeEnum::getEntityClassname($relationshipEntityType));

            // Validate each relationship object
            foreach ($validationData as $data) {
                if (is_array($data) == false || in_array('id', $data) == false || in_array('type', $data) == false) {
                    // Relationship object does not contain required details
                    $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData);
                    $error->addErrorSource('pointer', '/data/relationships/'.$name);
                    $this->addError($error);
                    continue;
                }
                if ($data['type'] != $relationshipEntityType) {
                    // Invalid entity type supplied for the relationship
                    $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidEntityType, "Entity type '".$data['type']."' is not valid for relationship '".$name."'. The correct entity type for this relationship is '".$relationshipEntityType."'.");
                    $error->addErrorSource('pointer', '/data/relationships/'.$name);
                    $this->addError($error);
                    continue;
                }

                // Load related entity
                $searchOptions = ['id' => $data['id']];
                if ($includeDeletedRecords == false) {
                    // Unless explicitly requested, filter out deleted records
                    $searchOptions['deleted'] = false;
                }
                $entity = $repository->findOneBy($searchOptions);
                if (is_null($entity) == true) {
                    // Invalid entity type supplied for the relationship
                    $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity with ID '".$data['id']."' not found for relationship '".$name."'.");
                    $error->addErrorSource('pointer', '/data/relationships/'.$name);
                    $this->addError($error);
                    continue;
                }

                // Add to hydrated resource linkage
                if ($relationshipType == EntityRelationshipTypeEnum::Single) {
                    $hydratedLinkage[$name] = $entity;
                } elseif ($relationshipType == EntityRelationshipTypeEnum::Collection) {
                    if (array_key_exists($name, $hydratedLinkage) == false) {
                        $hydratedLinkage[$name] = [];
                    }
                    $hydratedLinkage[$name][] = $entity;
                }
            }
        }

        return $hydratedLinkage;
    }

    /**
     * Check if the entity associated with this service requires an audit trail transaction
     *
     * @return bool
     */
    protected function requiresTransaction() {
        $relationships = $this->getEntityPublicRelationships();
        if (array_key_exists('transaction', $relationships)) {
            return true;
        }

        return false;
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
     * Generate an Transaction audit trail entity based on metadata in request payload
     *
     * @param array $meta
     * @return Tranquility\Data\Entities\System\Transaction
     */
    protected function createTransaction(array $meta) {
        // If this entity does not require an audit trail transaction, return null object
        if ($this->requiresTransaction() == false) {
            return null;
        }

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
        $searchOptions = array('clientId' => $clientId);
        return $repository->findOneBy($searchOptions);
    }

    /**
     * Create a new error object, based on the error code
     *
     * @param int $errorCode
     * @param string $description
     * @return \Tranquility\App\Errors\AbstractError
     */
    protected function createError(int $errorCode, string $description = null) {
        // Get error class details and default message
        $errorDetail = ApplicationErrorCodeEnum::getErrorDetails($errorCode);
        if (is_null($description)) {
            $description = $errorDetail['message'];
        }

        // Create error
        $error = new $errorDetail['errorClassname']($errorCode, $description);
        return $error;
    }

    /**
     * Add an error that has occurred during service processing
     *
     * @param \Tranquility\App\Errors\AbstractError $error
     * @return void
     */
    protected function addError(AbstractError $error) {
        $this->errors->addError($error);
    }

    /**
     * Check if any errors have occurred during service processing
     *
     * @return boolean
     */
    protected function hasErrors() {
        if (count($this->errors) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns a collection of errors that occurred during service processing
     *
     * @return Tranquility\App\Errors\Helpers\ErrorCollection
     */
    protected function getErrors() {
        return $this->errors;
    }
}