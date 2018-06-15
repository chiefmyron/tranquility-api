<?php namespace Tranquility\Resources;

// Validation classes
use Valitron\Validator as Validator;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;
use Tranquility\System\Enums\TransactionSourceEnum as TransactionSourceEnum;

abstract class AbstractResource {
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
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Define standard validation rules that are required for all entities
        $this->validationRuleGroups['default'][] = array('field' => 'updateDateTime', 'ruleType' => 'dateFormat', 'params' => ['Y-m-d H:i:s'], 'message' => 'message_10010_invalid_datetime_format');
        $this->validationRuleGroups['default'][] = array('field' => 'transactionSource', 'ruleType' => 'in', 'params' => [TransactionSourceEnum::getValues(), 'message' => 'message_10009_invalid_transaction_source_code']);
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @abstract
     * @return string
     */
    abstract public function getEntityClassname();

    /**
     * Validate a data array against the defined rules for the resource
     * 
     * @param  array  $data
     * @param  array  $groups  The set of validation groups to use when validating. Runs rules in the 'default' group unless otherwise specified.
     * @return mixed  True if valid, an array of messages if invalid
     */
    public function validate($data, $groups = array('default')) {
        // Create validator instance for the input data
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
            $validationRule = $validator->rule($rule['ruleType'], $rule['field'], $rule['params']);
            if (isset($rule['message'])) {
                $validationRule->message($rule['message']);
            }
        }

        // Perform the validation
        $result = $validator->validate();
        if ($result === false) {
            $errors = $validator->errors();
            $errorCollection = array();
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorDetail = array();
                    $errorDetail['source'] = ["pointer" => "/data/attributes/".$field];
                    $errorDetail['status'] = HttpStatus::UnprocessableEntity;
                    $errorDetail['code'] = "10002";
                    $errorDetail['title'] = "Field validation error";
                    $errorDetail['detail'] = $message;
                    $errorCollection[] = $errorDetail;
                }
                
            }

            return $errorCollection;
        }

        return true;
    }

    /**
	 * Perform a text search on the entity
	 *
	 * @param  mixed  $searchTerm        Either a search term string, or an array of search term strings
	 * @param  array  $orderConditions   Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage    If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex  Index of the record to start the result set from. Defaults to zero.
	 * @return array
	 */
	public function search($searchTerms, $orderConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0) {
		$fields = $this->_getSearchableFields();

		// Handle multiple search terms
		if (is_string($searchTerms)) {
			$searchTerms = array($searchTerms);
		}

		// Set up search terms
		$filterConditions = array();
		foreach ($fields as $fieldName) {
			foreach ($searchTerms as $term) {
				$filterConditions[] = array($fieldName, 'LIKE', '%'.$term.'%', 'OR');
			}
        }

		return $this->all($filterConditions, $orderConditions, $resultsPerPage, $startRecordIndex);
	}

    /**
	 * Retrieve all entities of this type
	 *
	 * @param  array  $filter            Used to specify additional filters to the set of results
	 * @param  array  $order             Used to specify order parameters to the set of results
	 * @param  int    $resultsPerPage    If zero or less, or null, the full result set will be returned
	 * @param  int    $startRecordIndex  Index of the record to start the result set from. Defaults to zero.
	 * @return array
	 */
	public function all($filterConditions = array(), $orderConditions = array(), $resultsPerPage = 0, $startRecordIndex = 0) {
		// If a 'deleted' filter has not been specified, default to select only records that have not been deleted
		/*$deleted = $this->_checkForFilterCondition($filterConditions, 'deleted');
		if ($deleted === false) {
			$filterConditions[] = array('deleted', '=', 0);
		}*/
				
        // Retrieve list of entities from repository
        $results = $this->getRepository()->all($filterConditions, $orderConditions, $resultsPerPage, $startRecordIndex);
        return $results;
    }

    /**
	 * Find a single entity by ID
	 *
	 * @param  int  $id  Entity ID of the object to retrieve
	 * @return Tranquility\Data\Entities\AbstractEntity
	 */
	public function find($id) {
        return $this->findBy('id', $id);
    }
    
    /**
     * Find a single entity by a specified field
     *
     * @param  string  $fieldName   Name of the field to search against
     * @param  string  $fieldValue  Value for entity search
     * @return Tranquility\Data\Entities\AbstractEntity
     */
	public function findBy($fieldName, $fieldValue) {
        $searchOptions = array($fieldName => $fieldValue);

        // Retrieve entity from repository
        $entity = $this->getRepository()->findBy($searchOptions);
		return $entity;
    }
    
    /**
     * Create a new record for an entity
     * 
     * @var  array  $data  Data used to create the new entity record
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function create(array $data) {
        // Validate input
        $validationRuleGroups = array('default', 'create');
        $result = $this->validate($data, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - create the entity
            $entity = $this->getRepository()->create($data);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Update an existing record for the specified entity
     * 
     * @var  int    $id    Record ID for the entity to update
     * @var  array  $data  New data to update against the existing record
     * @return  Tranquility\Data\Entities\AbstractEntity
     */
    public function update(int $id, array $data) {
        // Validate input
        $validationRuleGroups = array('default', 'update');
        $result = $this->validate($data, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - update the entity
            $entity = $this->getRepository()->update($id, $data);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Mark an existing entity record as deleted
     * 
     * @var  int    $id    Record ID for the entity to delete
     * @var  array  $data  Audit trail details to be attached to the deleted record
     * @return boolean
     */
    public function delete(int $id, array $data) {
        // Validate input
        $validationRuleGroups = array('default', 'delete');
        $result = $this->validate($data, $validationRuleGroups);
        if ($result === true) {
            // Data is valid - delete the entity
            $entity = $this->getRepository()->delete($id, $data);
            return $entity;
        } else {
            // Data is not valid - return error messages
            return $result;
        }
    }

    /**
     * Get the Repository associated with the Entity for this resource
     * 
     * @return Tranquility\Data\Repositories\Repository
     */
    protected function getRepository() {
        return $this->entityManager->getRepository($this->getEntityClassname());
    }
}