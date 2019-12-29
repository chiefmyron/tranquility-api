<?php namespace Tranquility\Data\Repositories\ReferenceDataObjects;

// Tranquility data entities
use Tranquility\Data\Repositories\AbstractRepository;
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject as Transaction;

class ReferenceDataObjectRepository extends AbstractRepository {

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
     * @param  array        $data         Input data to create the record
     * @param  Transaction  $transaction  Audit trail transaction entity
     * @return mixed
     */
    public function create(array $data, array $relationships, Transaction $transaction = null) {
        throw new \Exception("ReferenceDataObjectRepository::create() not implemented.");
    }
    
    /**
     * Updates an existing record
     *
     * @param  AbstractEntity  $entity       The updated entity to persist
     * @param  Transaction     $transaction  Audit trail transaction entity
     * @return AbstractEntity
     */ 
    public function update(AbstractEntity $entity, Transaction $transaction = null) {
        throw new \Exception("ReferenceDataObjectRepository::update() not implemented.");
    }
    
    /**
	 * Logically delete an existing record
	 *
	 * @param  AbstractEntity  $entity       The entity to logically delete
     * @param  Transaction     $transaction  Audit trail transaction entity
	 * @return AbstractEntity
	 */
    public function delete(AbstractEntity $entity, Transaction $transaction = null) {
        throw new \Exception("ReferenceDataObjectRepository::delete() not implemented.");
    }
}