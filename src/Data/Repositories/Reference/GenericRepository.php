<?php namespace Tranquillity\Data\Repositories\Reference;

// Entity classes
use Tranquillity\Data\Entities\AbstractEntity;
use Tranquillity\Data\Repositories\AbstractRepository;

class GenericRepository extends AbstractRepository {

    /**
     * Finds reference data entry by code.
     *
     * @param  string  $code  Reference data primary key code
     * @return AbstractReferenceDataObject 
     */
    public function findByCode($code) {
        // If no code is provided, then return null immediately
        if (is_null($code)) {
            return null;
        }

        // Start creation of query
        $entityName = $this->getEntityName();
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder->select('e')
                     ->from($entityName, 'e')
                     ->where('e.code = :code')
                     ->andWhere('e.effectiveFrom <= CURRENT_TIMESTAMP()')
                     ->andWhere(
                         $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->gte('e.effectiveUntil', 'CURRENT_TIMESTAMP()'),
                            $queryBuilder->expr()->isNull('e.effectiveUntil')
                        )
                     )
                     ->setParameter('code', $code);
        $query = $queryBuilder->getQuery();
        return $query->getOneOrNullResult();
    }

    /**
     * Creates a new record
     * 
     * @param  array             $data         Input data to create the record
     * @return mixed
     */
    public function create(array $data) {
        throw new \Exception(self::class . "::create() not implemented.");
    }
    
    /**
     * Updates an existing record
     *
     * @param  AbstractEntity    $entity       The updated entity to persist
     * @return AbstractEntity
     */ 
    public function update(AbstractEntity $entity) {
        throw new \Exception(self::class . "::update() not implemented.");
    }
    
    /**
	 * Logically delete an existing record
	 *
	 * @param  AbstractEntity    $entity       The entity to logically delete
	 * @return AbstractEntity
	 */
    public function delete(AbstractEntity $entity) {
        throw new \Exception(self::class . "::delete() not implemented.");
    }
}