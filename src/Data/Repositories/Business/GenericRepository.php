<?php namespace Tranquillity\Data\Repositories\Business;

// Entity classes
use Tranquillity\Data\Entities\AbstractEntity;
use Tranquillity\Data\Entities\Business\AbstractBusinessEntity;
use Tranquillity\Data\Entities\System\AuditTransactionEntity as AuditTransaction;
use Tranquillity\Data\Repositories\AbstractRepository;

class GenericRepository extends AbstractRepository {

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
     * Creates a new business object entity record.
     * 
     * @param  array             $attributes     Input data to create the record
     * @param  AuditTransaction  $transaction    Audit trail transaction entity
     * @return AbstractBusinessEntity
     */
    public function create(array $attributes, array $relationships, AuditTransaction $transaction = null) {
        // Audit trail transaction information is mandatory when creating a BusinessObject entity
        if (is_null($transaction)) {
            throw new \Exception("A '" . AuditTransaction::class . "' object must be supplied when creating a '" . AbstractBusinessEntity::class . "' entity");
        }
        
        // Create new audit trail transaction record
        $this->_em->persist($transaction);
        
        // Create new entity record, with the audit trail attached
        $entityName = $this->getEntityName();
        $entity = new $entityName($attributes);
        $entity->version = 1; // Force version for new records to be 1

        // Add related entities 
        $entity->populate($relationships);

        // Add audit trail transaction
        $entity->transaction = $transaction;

        // Persist newly created entity and return
        $this->_em->persist($entity);
        $this->_em->flush();
		return $entity;
    }
    
    /**
     * Updates an existing entity record, and tracks field-level changes in the audit log table
     *
     * @param  AbstractBusinessEntity  $entity        The updated entity to persist
     * @param  AuditTransaction        $transaction   Audit trail transaction entity
     * @return AbstractBusinessEntity
     */ 
    public function update(AbstractEntity $entity, AuditTransaction $transaction = null) {
        // Audit trail transaction information is mandatory when creating a AbstractBusinessEntity entity
        if (is_null($transaction)) {
            throw new \Exception("A '" . AuditTransaction::class . "' object must be supplied when updating an '" . AbstractBusinessEntity::class . "' entity");
        }

        // Make sure that the entity supplied is an AbstractBusinessEntity Entity
        if (($entity instanceof AbstractBusinessEntity) == false) {
            throw new \Exception("The '" . self::class ."' can only be used with a subclass of an '" . AbstractBusinessEntity::class . "' entity.");
        }

        // Create new audit trail transaction record
        $this->_em->persist($transaction);

        // Increment version number and add audit transaction for updated entity before persisting
        $entity->version = ($entity->version + 1);
        $entity->transaction = $transaction;
        $this->_em->persist($entity);
        $this->_em->flush();
        
        // Return updated entity
        return $entity;
    }
    
    /**
	 * Logically deletes an existing entity record
	 *
	 * @param  AbstractBusinessEntity  $entity        The updated entity to persist
     * @param  AuditTransaction        $transaction   Audit trail transaction entity
     * @return AbstractBusinessEntity
     */ 
    
	public function delete(AbstractEntity $entity, AuditTransaction $transaction = null) {
        // Set the deleted flag on the entity and update
        $entity->deleted = 1;
        return $this->update($entity, $transaction);
	}
}