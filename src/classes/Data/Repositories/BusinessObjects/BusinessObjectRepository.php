<?php namespace Tranquility\Data\Repositories\BusinessObjects;

// Tranquility data entities
use Tranquility\Data\Repositories\AbstractRepository;
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;
use Tranquility\Data\Entities\SystemObjects\TagSystemObject as Tag;
use Tranquility\Data\Entities\SystemObjects\TransactionSystemObject as Transaction;

class BusinessObjectRepository extends AbstractRepository {

    /**
     * Finds entities by a set of criteria.
     *
     * @param  array       $criteria
     * @param  array|null  $orderBy
     * @param  int|null    $limit
     * @param  int|null    $offset
     *
     * @return array An array of matched entities
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
        // By default, do not find deleted records
        if (!isset($criteria['deleted'])) {
            $criteria['deleted'] = 0;
        }
        
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
    
    /**
     * Creates a new business object entity record
     * 
     * @param  array        $data         Input data to create the record
     * @param  Transaction  $transaction  Audit trail transaction entity
     * @return BusinessObject
     */
    public function create(array $data, Transaction $transaction = null) {
        // Audit trail transaction information is mandatory when creating a BusinessObject entity
        if (is_null($transaction)) {
            throw new \Exception("A '" . Transaction::class . "' object must be supplied when creating a '" . BusinessObject::class . "' entity");
        }
        
        // Create new audit trail transaction record
        $this->_em->persist($transaction);
        
        // Create new entity record, with the audit trail attached
        $entityName = $this->getEntityName();
        $entity = new $entityName($data);
        $entity->version = 1; // Force version for new records to be 1
        $entity->transaction = $transaction;
        $this->_em->persist($entity);
        $this->_em->flush();
		
		// Return newly created entity
		return $entity;
    }
    
    /**
     * Updates an existing entity record, and moves the old version of the record
     * into a historical table
     *
     * @param  AbstractEntity  $entity        The updated entity to persist
     * @param  Transaction     $transaction   Audit trail transaction entity
     * @return BusinessObject
     */ 
    public function update(AbstractEntity $entity, Transaction $transaction = null) {
        // Audit trail transaction information is mandatory when creating a BusinessObject entity
        if (is_null($transaction)) {
            throw new \Exception("A '" . Transaction::class . "' object must be supplied when creating a '" . BusinessObject::class . "' entity");
        }

        // Make sure that the entity supplied is a BusinessObject Entity
        if (($entity instanceof BusinessObject) == false) {
            throw new \Exception("The BusinessObjectRepository can only be used with a subclass of a BusinessObject entity");
        }

        // Get the current, unmodified version of the entity from the database
        $existingEntity = $this->find($entity->id);
        $existingTransaction = $existingEntity->transaction;

        // Create historical version of entity
        $entityName = $this->getEntityName();
        $historyClassName = call_user_func($entityName.'::getHistoricalEntityClass');
        $historicalEntity = new $historyClassName($existingEntity);
        $historicalEntity->transaction = $existingTransaction;
        $this->_em->persist($historicalEntity);
        
        // Create new audit trail transaction record
        $this->_em->persist($transaction);
        
        // Update existing entity record with new details, incremented version number
        // and new audit trail details
        $entity->version = ($historicalEntity->version + 1);
        $entity->transaction = $transaction;
        $this->_em->persist($entity);
        $this->_em->flush();
        
        // Return updated entity
        return $entity;
    }
    
    /**
	 * Logically delete an existing entity record
	 *
	 * @param  AbstractEntity  $entity        The updated entity to persist
     * @param  Transaction     $transaction   Audit trail transaction entity
     * @return BusinessObject
     */ 
    
	public function delete(AbstractEntity $entity, Transaction $transaction = null) {
        // Set the deleted flag on the entity and update
        $entity->deleted = 1;
        return $this->update($entity, $transaction);
	}
}