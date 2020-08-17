<?php namespace Tranquility\Data\Repositories;

// ORM class libraries
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\System\AuditTransactionEntity as AuditTransaction;

// Tranquility class libraries
use Tranquility\Support\ArrayHelper as Arr;
use Tranquility\System\Enums\FilterOperatorEnum;

abstract class AbstractRepository extends EntityRepository {

    /**
     * Retrieve a set of all records
     *
     * Optional filter and pagination conditions can be specified for the result set
     *
     * @param array $filterConditions
     * @param array $orderConditions
     * @param int   $pageNumber
     * @param int   $resultsPerPage
     * @return array
     */
    public function all($filterConditions = array(), $orderConditions = array(), $pageNumber = 0, $resultsPerPage = 0) {
        // Start creation of query
        $entityName = $this->getEntityName();
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('e')->from($entityName, 'e');
        
        // Add other filter conditions
        $queryBuilder = $this->_addQueryFilters($queryBuilder, $filterConditions, $orderConditions);
        $query = $queryBuilder->getQuery();
        
        // If pagination options have been supplied, return a paginated set
        if ($resultsPerPage > 0 && $pageNumber > 0) {
            $numRecordsToSkip = $resultsPerPage * ($pageNumber - 1);
            $query->setFirstResult($numRecordsToSkip);
            $query->setMaxResults($resultsPerPage);
            return new Paginator($query, true);
        }

        // No pagination, so return the full result set
        return $query->getResult();
    }

    /**
     * Creates a new record
     * 
     * @param  array             $attributes     Input data to create the record
     * @param  AuditTransaction  $transaction    Audit trail transaction entity
     * @return AbstractEntity
     */
    abstract public function create(array $attributes, array $relationships);
    
    /**
     * Updates an existing record
     *
     * @param  AbstractEntity    $entity       The updated entity to persist
     * @param  AuditTransaction  $transaction  Audit trail transaction entity
     * @return AbstractEntity
     */ 
    abstract public function update(AbstractEntity $entity);
    
    /**
	 * Logically delete an existing record
	 *
	 * @param  AbstractEntity    $entity       The entity to logically delete
     * @param  AuditTransaction  $transaction  Audit trail transaction entity
	 * @return AbstractEntity
	 */
    abstract public function delete(AbstractEntity $entity);

    /**
	 * Used to add additional query conditions, ordering and set limits to a selection query
	 *
	 * @param \Doctrine\ORM\QueryBuilder $query   The initial selection query
	 * @param array  $filterConditions            Array of filter conditions to append to the selection query
	 * @param array  $orderConditions             Array of order conditions to append to the selection query
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function _addQueryFilters($queryBuilder, $filterConditions = array(), $orderConditions = array()) {
		$parameters = array();
        $parameterCounter = 0;
        
        // Add filter conditions
		foreach ($filterConditions as $filter) {
            // Get filter details
            $expression = null;
            $whereType = null;
            $fieldName = $filter[0];
            $operator = trim(strtolower(Arr::get($filter, 1, FilterOperatorEnum::Equals)));

            // Build expression for this filter
            switch ($operator) {
                case FilterOperatorEnum::Equals:
                    $operator = '=';
                    break;
                case FilterOperatorEnum::NotEquals:
                    $operator = '<>';
                    break;
                case FilterOperatorEnum::GreaterThan:
                    $operator = '>';
                    break;
                case FilterOperatorEnum::GreaterThanEqual:
                    $operator = '>=';
                    break;
                case FilterOperatorEnum::LessThan:
                    $operator = '<';
                    break;
                case FilterOperatorEnum::LessThanEqual:
                    $operator = '<=';
                    break;
                case FilterOperatorEnum::IsNull:
                    $expression = $queryBuilder->expr()->isNull('e.'.$fieldName);
                    $whereType = trim(strtoupper(Arr::get($filter, 2, 'AND')));
                    break;
                case FilterOperatorEnum::IsNotNull:
                    $expression = $queryBuilder->expr()->isNotNull('e.'.$fieldName);
                    $whereType = trim(strtoupper(Arr::get($filter, 2, 'AND')));
                    break;
                case FilterOperatorEnum::In:
                    $expression = $queryBuilder->expr()->in('e.'.$fieldName, '?'.$parameterCounter);
                    $parameters[$parameterCounter] = $filter[2];
                    $parameterCounter++;
                    $whereType = trim(strtoupper(Arr::get($filter, 3, 'AND')));
                    break;
                case FilterOperatorEnum::NotIn:
                    $expression = $queryBuilder->expr()->notIn('e.'.$fieldName, '?'.$parameterCounter);
                    $parameters[$parameterCounter] = $filter[2];
                    $parameterCounter++;
                    $whereType = trim(strtoupper(Arr::get($filter, 3, 'AND')));
                    break;
                case FilterOperatorEnum::Like:
                    $expression = $queryBuilder->expr()->like('LOWER(e.'.$fieldName.')', '?'.$parameterCounter);
                    $parameters[$parameterCounter] = strtolower($filter[2]); // Case-insensitive searching
                    $parameterCounter++;
                    $whereType = trim(strtoupper(Arr::get($filter, 3, 'AND')));
                    break;
                case FilterOperatorEnum::NotLike:
                    $expression = $queryBuilder->expr()->notLike('LOWER(e.'.$fieldName.')', '?'.$parameterCounter);
                    $parameters[$parameterCounter] = strtolower($filter[2]); // Case-insensitive searching
                    $parameterCounter++;
                    $whereType = trim(strtoupper(Arr::get($filter, 3, 'AND')));
                    break;
                default:
                    $operator = '=';
                    break;
            }

            // If we have an expression, add it to the query now
            if (!is_null($expression)) {
                // Existing expression
                if ($whereType == 'AND') {
                    $queryBuilder->andWhere($expression);
                } elseif ($whereType == 'OR') {
                    $queryBuilder->orWhere($expression);
                }
            } else {
                // Standard SQL comparision 
                $whereType = trim(strtoupper(Arr::get($filter, 3, 'AND')));
                if ($whereType == 'AND') {
                    $queryBuilder = $queryBuilder->andWhere('e.'.$fieldName.' '.$operator.' ?'.$parameterCounter);
                } elseif ($whereType == 'OR') {
                    $queryBuilder = $queryBuilder->orWhere('e.'.$fieldName.' '.$operator.' ?'.$parameterCounter);
                }
                $parameters[$parameterCounter] = $filter[2];
                $parameterCounter++;
            }
        }
        
		// Add order statements
		foreach ($orderConditions as $order) {
            $queryBuilder = $queryBuilder->addOrderBy('e.'.$order[0], $order[1]);
		} 
        
        // Add parameters
        foreach ($parameters as $key => $value) {
            $queryBuilder = $queryBuilder->setParameter($key, $value);
        }

		return $queryBuilder;
	}
}