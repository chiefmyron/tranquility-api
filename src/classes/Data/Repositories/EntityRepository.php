<?php namespace Tranquility\Data\Repositories;

use Tranquility\Data\Entities\Extensions\TagEntityExtension        as Tag;
use Tranquility\Data\Entities\Extensions\AuditTrailEntityExtension as AuditTrail;

class EntityRepository extends AbstractRepository {

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
     * Creates a new entity record
     * 
     * @param  array  $data  Input data to create the record
     * @return \Tranquility\Data\Entities\Entity
     */
    public function create(array $data) {
		// Create new audit trail record
		//$auditTrail = new AuditTrail($data);
        //$this->_em->persist($auditTrail);
        
        // Create new entity record, with the audit trail attached
        $entityName = $this->getEntityName();
        $entity = new $entityName($data);
        $entity->version = 1; // Force version for new records to be 1
        //$entity->setAuditTrail($auditTrail);
        $this->_em->persist($entity);
        $this->_em->flush();
		
		// Return newly created entity
		return $entity;
    }
    
    /**
     * Updates an existing entity record, and moves the old version of the record
     * into a historical table
     *
     * @param int   $id    Business object entity ID
     * @param array $data  Updated values to apply to the entity
     * @return \Tranquility\Data\BusinessObjects\Entity
     */ 
    public function update($id, array $data) {
        // Retrieve existing record
        $entity = $this->find($id);
        $entityName = $this->getEntityName();
        
        // Create historical version of entity
        $historyClassName = call_user_func($entityName.'::getHistoricalEntityClass');
        $historicalEntity = new $historyClassName($entity);
        $historicalEntity->setAuditTrail($entity->getAuditTrail());
        $this->_em->persist($historicalEntity);
        
        // Create new audit trail record
		$auditTrail = new AuditTrail($data);
        $this->_em->persist($auditTrail);
        
        // Update existing entity record with new details, incremented version number
        // and new audit trail details
        unset($data['version']);  // Ensure passed data does not override internal versioning
        $entity->populate($data);
        $entity->version = ($entity->version + 1);
        $entity->setAuditTrail($auditTrail);
        $this->_em->persist($entity);
        $this->_em->flush();
        
        // Return updated entity
        return $entity;
    }
    
    /**
	 * Logically delete an existing entity record
	 *
	 * @param int   $id    Entity ID of the record to delete
	 * @param array 
	 */
	public function delete($id, array $data) {
        // Add deleted flag to data array
        $data['deleted'] = 1;
        return $this->update($id, $data);
	}
    
    /**
     * Associate a tag with the entity
     *
     * @param int $id  Business object entity ID
     * @param Tag $tag Tag to associate
     * @return \Tranquility\Data\BusinessObjects\Entity
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
     * @param int $id  Business object entity ID
     * @param Tag $tag Tag to disassociate
     * @return \Tranquility\Data\BusinessObjects\Entity
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
     * @param int   $id             Business object entity ID
     * @param array $tagCollection  Array of Tag objects to associate
     * @return \Tranquility\Data\BusinessObjects\Entity
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