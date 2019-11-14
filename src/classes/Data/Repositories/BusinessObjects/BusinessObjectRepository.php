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
    public function create(array $data, Transaction $audit = null) {
        // Audit trail transaction information is mandatory when creating a BusinessObject entity
        if (is_null($transaction)) {
            throw \Exception("A '" . Transaction::class . "' object must be supplied when creating a '" . BusinessObject::class . "' entity");
        }
        
        // Create new audit trail transaction record
        $this->_em->persist($transaction);
        
        // Create new entity record, with the audit trail attached
        $entityName = $this->getEntityName();
        $entity = new $entityName($data);
        $entity->version = 1; // Force version for new records to be 1
        $entity->audit = $audit;
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
            throw \Exception("A '" . Transaction::class . "' object must be supplied when creating a '" . BusinessObject::class . "' entity");
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
    
    /**
     * Associate a tag with the entity
     *
     * @param  int  $id   Business object entity ID
     * @param  Tag  $tag  Tag to associate
     * @return BusinessObject
     */ 
    public function addTag($id, $tag) {
        Log::info('Adding tag "'.$tag.'" to entity ID '.$id);

        // Retrieve existing record
        $entity = $this->find($id);
        $entity->addTag($tag);
        $this->_em->flush();
        
        // Return updated entity
        return $entity;
    }
    
    /**
     * Disassociate a tag from an entity
     *
     * @param  int  $id   Business object entity ID
     * @param  Tag  $tag  Tag to disassociate
     * @return BusinessObject
     */ 
    public function removeTag($id, $tag) {
        Log::info('Removing tag "'.$tag.'" from entity ID '.$id);

        // Retrieve existing record
        $entity = $this->find($id);
        $entity->removeTag($tag);
        $this->_em->flush();
        
        // Return updated entity
        return $entity;
    }
    
    /**
     * Sets the list of tags to be associated with an entity
     * 
     * @param  int    $id             Business object entity ID
     * @param  array  $tagCollection  Array of Tag objects to associate
     * @return BusinessObject
     */
    public function setTags($id, array $tagCollection) {
        // Retrieve existing record
        $entity = $this->find($id);
        
        // Get existing tag collection for entity
        $existingTags = $entity->getTags();
        
        // Determine which tags need to be added
        $adds = array_diff($tagCollection, $existingTags);
        foreach($adds as $addTag) {
            $entity = $this->addTag($id, $addTag);
        }
        
        // Determine which tags need to be removed from the collection
        $removes = array_diff($existingTags, $tagCollection);
        foreach ($removes as $removeTag) {
            $entity = $this->removeTag($id, $removeTag);
        }
        
        // Return updated entity
        return $entity;
    }
}