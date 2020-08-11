<?php namespace Tranquility\Data\Repositories\System;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Entities\System\AbstractSystemEntity;
use Tranquility\Data\Repositories\AbstractRepository;

class GenericRepository extends AbstractRepository {

    /**
     * Creates a new system object entity record.
     * 
     * @param  array           $attributes     Input data to create the record
     * @return \Tranquility\Data\Entities\System\AbstractSystemEntity
     */
    public function create(array $data) {
        // Create new entity extension record
        $entityName = $this->getEntityName();
        $entity = new $entityName($data);
        $this->_em->persist($entity);
        $this->_em->flush();
		
		// Return newly created system object entity record
		return $entity;
    }

    /**
     * Updates an existing system object entity record
     *
     * @param  AbstractEntity  $entity       The updated entity to persist
     * @return \Tranquility\Data\Entities\System\AbstractSystemEntity
     */ 
    public function update(AbstractEntity $entity) {
        // Make sure that the entity supplied is an AbstractSystemObject entity
        if (($entity instanceof AbstractSystemEntity) == false) {
            throw new \Exception("The " . GenericRepository::class . " can only be used with a subclass of an " . AbstractSystemEntity::class . " entity.");
        }

        // Update existing record with new details
        $this->_em->persist($entity);
        $this->_em->flush();
        
        // Return updated entity extension record
        return $entity;
    }
    
    /**
	 * Logically delete an existing system object entity record
	 *
	 * @param  AbstractEntity  $entity  The entity to logically delete
     * @return \Tranquility\Data\Entities\System\AbstractSystemEntity
     */ 
    
	public function delete(AbstractEntity $entity) {
        // Set the deleted flag on the entity and update
        $entity->deleted = 1;
        return $this->update($entity);
	}
}