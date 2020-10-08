<?php namespace Tranquillity\Services;

// Vendor class libraries
use Valitron\Validator as Validator;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;
use Tranquillity\App\Errors\AbstractError;

// Framework class libraries
use Tranquillity\App\Errors\Helpers\ErrorCollection;
use Tranquillity\Data\Entities\AbstractEntity as Entity;
use Tranquillity\Support\ArrayHelper as Arr;
use Tranquillity\System\Utility as Utility;
use Tranquillity\System\Enums\ApplicationErrorCodeEnum;
use Tranquillity\System\Enums\EntityTypeEnum;

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
     * @var \Tranquillity\Data\Entities\AbstractEntity
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
     * @var \Tranquillity\App\Errors\Helpers\ErrorCollection
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
     * @param  array|Entity  $data
     * @param  array         $groups  The set of validation groups to use when validating. Runs rules in the 'default' group unless otherwise specified.
     * @return mixed  True if valid, an array of messages if invalid
     */
    public function validateAttributes($data, array $groups = array('default')) {
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
	public function search($searchTerms, array $orderConditions = array(), int $resultsPerPage = 0, int $startRecordIndex = 0, bool $includeDeletedRecords = false) {
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
	 * @return array|\Tranquillity\App\Errors\Helpers\ErrorCollection  Array of entities if successful, or an error collection if validation failed
	 */
	public function all(array $filterConditions = array(), array $sortingConditions = array(), int $resultsPerPage = 0, int $startRecordIndex = 0) {
        // Get the list of public fields
        $publicFields = $this->getEntityPublicFields();
        
        // Validate order conditions
        foreach ($sortingConditions as $sortField) {
            if (array_key_exists($sortField[0], $publicFields) == false) {
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationInvalidQueryParameter, sprintf("'%s' is not a sortable field", $sortField[0]));
                $error->addErrorSource('parameter', 'sort');
                $this->errors->addError($error);
            }
        }

        // Validate filter conditions
        foreach ($filterConditions as $filterField) {
            if (array_key_exists($filterField[0], $publicFields) == false) {
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationInvalidQueryParameter, sprintf("'%s' is not a filterable field", $filterField[0]));
                $error->addErrorSource('parameter', 'filter');
                $this->errors->addError($error);
            }
        }

        // If validation failed, return the error collection
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while validating attributes and relationships
        }
				
        // Retrieve list of entities from repository
        $results = $this->getRepository()->all($filterConditions, $sortingConditions, $resultsPerPage, $startRecordIndex);
        return $results;
    }

    /**
	 * Find a single entity by ID
	 *
	 * @param  string  $id  Entity ID of the object to retrieve
     * @param  bool    $includeDeletedRecords  Includes soft-deleted records if true
	 * @return mixed Returns Tranquillity\Data\Entities\AbstractEntity if found, otherwise false
	 */
	public function find(string $id, bool $includeDeletedRecords = false) {
        return $this->findOneBy('id', $id, $includeDeletedRecords);
    }
    
    /**
     * Find a single entity by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @return mixed Returns Tranquillity\Data\Entities\AbstractEntity if found, otherwise false
     */
	public function findOneBy(string $fieldName, string $fieldValue) {
        // Retrieve entity from repository
        $searchOptions = [$fieldName => $fieldValue];
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
     * @return array Returns array of Tranquillity\Data\Entities\AbstractEntity objects
     */
	public function findBy(string $fieldName, string $fieldValue) {
        // Retrieve entity collection from repository
        $searchOptions = [$fieldName => $fieldValue];
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
     * @return \Tranquillity\Data\Entities\AbstractEntity
     * @abstract
     */
    public abstract function create(array $payload);

    /**
     * Update an existing record for the specified entity
     * 
     * @var  string  $id       Record ID for the entity to update
     * @var  array   $payload  New data to update against the existing record
     * @return  Tranquillity\Data\Entities\AbstractEntity
     * @abstract
     */
    public abstract function update(string $id, array $payload);
        
    /**
     * Mark an existing entity record as deleted
     * 
     * @var  string  $id       Record ID for the entity to delete
     * @var  array   $payload  Audit trail details to be attached to the deleted record
     * @return \Tranquillity\Data\Entities\AbstractEntity
     * @abstract
     */
    public abstract function delete(string $id, array $payload);

    /**
     * Add one or more members to a relationship for an entity
     *
     * @param string  $id
     * @param string  $relationshipName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function addRelationshipMembers(string $id, string $relationshipName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relationshipName]['collection'];

        // Validate relationship details
        if ($relationshipCollection == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidType, "The relationship named '".$relationshipName."' is for a single entity - cannot accept a collection of entities.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (is_null($data) == true || count($data) <= 0) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When adding entities to a relationship, at least one entity must be provided in the request.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (Arr::isAssoc($data) == true) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When adding entities to a relationship, 'data' must contain an array of resource identifier objects representing the entities to add to the collection.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (array_key_exists($relationshipName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relationshipName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Load the existing entity
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // Add specified entities to the collection
        $counter = 0;
        foreach ($data as $relatedEntityDetail) {
            $relatedEntity = $this->hydrateResourceIdentifier($relatedEntityDetail['id'], $relatedEntityDetail['type']);
            if (is_null($relatedEntity) == true) {
                // Invalid entity type supplied for the relationship
                $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity of type '". $relatedEntityDetail['type']."' with ID '".$relatedEntityDetail['id']."' was not found.");
                $error->addErrorSource('pointer', '/data/'.$counter.'/id');
                $this->addError($error);
                continue;
            }

            // If related entity does not already exist in the collection, add it now
            if ($entity->$relationshipName->contains($relatedEntity) == false) {
                $entity->addToCollection($relationshipName, $relatedEntity);
            }
        }
        
        // Check if errors were encountered while loading related entities
        if ($this->hasErrors() == true) {
            return $this->getErrors();  
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
     * @param string  $relationshipName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function updateRelationshipMembers(string $id, string $relationshipName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data');

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relationshipName]['collection'];

        // Validate relationship details
        if (array_key_exists($relationshipName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relationshipName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == true && is_null($data) == true) {
            // Cannot clear a collection by supplying 'null'
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "The relationship named '".$relationshipName."' is for a collection - 'data' member must contain either an array of resource identifiers (or an empty array to clear the relationship).");
            $this->addError($error);
            return $this->getErrors();
        }
        if ($relationshipCollection == false && is_array($data) == true && Arr::isAssoc($data) == false) {
            // Cannot clear a single entity relationship by supplying an array
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "The relationship named '".$relationshipName."' is for a single entity - 'data' member must contain either a single resource identifier (or null to clear the relationship).");
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
            $entity->clearCollection($relationshipName);
        }

        // Updating the relationship depends on what data is provided and the type of relationship
        if ($relationshipCollection == false && is_null($data) == true) {
            // Clear the relationship
            $entity->$relationshipName = null;
        } elseif ($relationshipCollection == false && is_null($data) == false) {
            // Update the existing relationship with a new entity
            $relatedEntity = $this->hydrateResourceIdentifier($data['id'], $data['type']);
            if (is_null($relatedEntity) == true) {
                // Invalid entity type supplied for the relationship
                $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity of type '". $data['type']."' with ID '".$data['id']."' was not found.");
                $error->addErrorSource('pointer', '/data/id');
                $this->addError($error);
            } else {
                // Update the relationship
                $entity->$relationshipName = $relatedEntity;
            }
        } elseif ($relationshipCollection == true) {
            // If we are working with a collection relationship, always clear the collection (it is either being cleared or replaced)
            $entity->clearCollection($relationshipName);

            // Update relationship with entity details
            $counter = 0;
            foreach ($data as $relatedEntityDetail) {
                $relatedEntity = $this->hydrateResourceIdentifier($relatedEntityDetail['id'], $relatedEntityDetail['type']);
                if (is_null($relatedEntity) == true) {
                    // Invalid entity type supplied for the relationship
                    $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity of type '". $relatedEntityDetail['type']."' with ID '".$relatedEntityDetail['id']."' was not found.");
                    $error->addErrorSource('pointer', '/data/'.$counter.'/id');
                    $this->addError($error);
                    continue;
                }

                // If related entity does not already exist in the collection, add it now
                if ($entity->$relationshipName->contains($relatedEntity) == false) {
                    $entity->addToCollection($relationshipName, $relatedEntity);
                }
            }
        }

        // Check if errors were encountered while loading related entities
        if ($this->hasErrors() == true) {
            return $this->getErrors();  
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
     * @param string  $relationshipName
     * @param array   $payload
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function deleteRelationshipMembers(string $id, string $relationshipName, array $payload) {
        // Get input attributes from data
        $meta = Utility::extractValue($payload, 'meta', array());
        $data = Utility::extractValue($payload, 'data', array());

        // Get allowed relationships for the entity
        $relationshipDetail = $this->getEntityPublicRelationships();
        $relationshipCollection = $relationshipDetail[$relationshipName]['collection'];

        // Validate relationship details
        if ($relationshipCollection == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidType, "The relationship named '".$relationshipName."' is for a single entity - cannot accept a collection of entities.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (is_null($data) == true || count($data) <= 0) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When deleting entities from a relationship, at least one entity must be provided in the request.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (Arr::isAssoc($data) == true) {
            // No related entities supplied
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipInvalidData, "When deleting entities from a relationship, 'data' must contain an array of resource identifier objects representing the entities to remove from the collection.");
            $this->addError($error);
            return $this->getErrors();
        }
        if (array_key_exists($relationshipName, $relationshipDetail) == false) {
            // Invalid relationship error
            $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$relationshipName."' for this entity.");
            $this->addError($error);
            return $this->getErrors();
        }

        // Load the existing entity
        $entity = $this->find($id);
        if ($this->hasErrors() == true) {
            return $this->getErrors();  // Errors encountered while retrieving the entity record
        }

        // Delete specified entities from the collection
        $counter = 0;
        foreach ($data as $relatedEntityDetail) {
            $relatedEntity = $this->hydrateResourceIdentifier($relatedEntityDetail['id'], $relatedEntityDetail['type']);
            if (is_null($relatedEntity) == true) {
                // Invalid entity type supplied for the relationship
                $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity of type '". $relatedEntityDetail['type']."' with ID '".$relatedEntityDetail['id']."' was not found.");
                $error->addErrorSource('pointer', '/data/'.$counter.'/id');
                $this->addError($error);
                continue;
            }

            // If related entity exists in the collection, delete it now
            if ($entity->$relationshipName->contains($relatedEntity) == true) {
                $entity->removeFromCollection($relationshipName, $relatedEntity);
            }
        }
        
        // Check if errors were encountered while loading related entities
        if ($this->hasErrors() == true) {
            return $this->getErrors();  
        }

        // Data is valid - update the entity
        $transaction = $this->createTransaction($meta);
        $entity = $this->getRepository()->update($entity, $transaction);
        return $entity;
    }

    /**
     * Get related entity
     * 
     * @var  string  $id                 ID for the parent entity
     * @var  string  $relatedEntityName  Name of the attribute that refers to the related entity
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    public function getRelatedEntity(string $id, string $relatedEntityName) {
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
            // If linkage is null or empty array, no further processing is required
            if (is_null($values) == true || count($values) <= 0) {
                $hydratedLinkage[$name] = $values;
                continue;
            }

            // Check relationship name is valid
            $validationData = $values['data'];
            if (array_key_exists($name, $entityRelationshipDetails) == false) {
                // Invalid relationship error
                $error = $this->createError(ApplicationErrorCodeEnum::ValidationRelationshipNotAllowed, "Cannot create or update a relationship named '".$name."' for this entity.");
                $error->addErrorSource('pointer', '/data/relationships/'.$name);
                $this->addError($error);
                continue;
            }

            // If this is a 'to-one' relationship, check that data is only a single relationship object
            $relationshipCollection = $entityRelationshipDetails[$name]['collection'];
            $relationshipEntityType = $entityRelationshipDetails[$name]['type'];
            $relationshipEntityClass = $entityRelationshipDetails[$name]['class'];
            if ($relationshipCollection == false) {
                // Put this into a collection to simplify the remaining validation logic
                $validationData = array($validationData);
            } 

            // Validate each relationship object
            foreach ($validationData as $data) {
                if (is_array($data) == false || array_key_exists('id', $data) == false || array_key_exists('type', $data) == false) {
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
                $entity = $this->hydrateResourceIdentifier($data['id'], $relationshipEntityClass);
                if (is_null($entity) == true) {
                    // Invalid entity type supplied for the relationship
                    $error = $this->createError(ApplicationErrorCodeEnum::RecordNotFound, "Entity with ID '".$data['id']."' not found for relationship '".$name."'.");
                    $error->addErrorSource('pointer', '/data/relationships/'.$name);
                    $this->addError($error);
                    continue;
                }

                // Add to hydrated resource linkage
                if ($relationshipCollection == true) {
                    // Collection relationship
                    if (array_key_exists($name, $hydratedLinkage) == false) {
                        $hydratedLinkage[$name] = [];
                    }
                    $hydratedLinkage[$name][] = $entity;
                } else {
                    // One-to-one relationship
                    $hydratedLinkage[$name] = $entity;
                }
            }
        }

        return $hydratedLinkage;
    }

    /**
     * Hydrate an individual resource identifier document into an entity
     *
     * @param string $id          ID of the entity to load
     * @param string $entityType  Type of entity to load
     * @return \Tranquillity\Data\Entities\AbstractEntity
     */
    protected function hydrateResourceIdentifier(string $id, string $entityType, array $searchOptions = []) {
        // Find repository for the entity type
        $entityClassname = EntityTypeEnum::getEntityClassname($entityType);
        $repository = $this->entityManager->getRepository($entityClassname);

        // Load related entity
        $searchOptions['id'] = $id;
        $entity = $repository->findOneBy($searchOptions);
        return $entity;
    }

    /**
     * Get the Repository associated with the Entity for this resource
     * 
     * @return Tranquillity\Data\Repositories\Repository
     */
    protected function getRepository() {
        return $this->entityManager->getRepository($this->getEntityClassname());
    }

    /**
     * Generate an Transaction audit trail entity based on metadata in request payload
     *
     * @param array               $meta
     * @param AbstractEntity|null $entityOld
     * @param AbstractEntity|null $entityNew
     * @return Tranquillity\Data\Entities\System\Transaction
     */
    abstract protected function createTransaction(array $meta, ?Entity $entityOld = null, ?Entity $entityNew = null);

    /**
     * Create a new error object, based on the error code
     *
     * @param int $errorCode
     * @param string $description
     * @return \Tranquillity\App\Errors\AbstractError
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
     * @param \Tranquillity\App\Errors\AbstractError $error
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
     * @return Tranquillity\App\Errors\Helpers\ErrorCollection
     */
    protected function getErrors() {
        return $this->errors;
    }
}