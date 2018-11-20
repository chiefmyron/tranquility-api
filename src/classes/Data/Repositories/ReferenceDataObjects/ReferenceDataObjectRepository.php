<?php namespace Tranquility\Data\Repositories\ReferenceDataObjects;

// Tranquility data entities
use Tranquility\Data\Repositories\AbstractRepository;
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;
use Tranquility\Data\Entities\SystemObjects\TagSystemObject as Tag;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

class ReferenceDataObjectRepository extends AbstractRepository {

    /**
     * Finds reference data entry by code.
     *
     * @param  string  $code  Reference data primary key code
     * @return AbstractReferenceDataObject 
     */
    public function findByCode(string $code) {
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
     * @param  array       $data   Input data to create the record
     * @param  AuditTrail  $audit  Audit trail object
     * @return mixed
     */
    public function create(array $data, AuditTrail $audit = null) {
        throw new \Exception("ReferenceDataObjectRepository::create() not implemented.");
    }
    
    /**
     * Updates an existing record
     *
     * @param  int         $id     Record ID
     * @param  array       $data   Updated values to apply to the entity
     * @param  AuditTrail  $audit  Audit trail object
     * @return mixed
     */ 
    public function update($id, array $data, AuditTrail $audit = null) {
        throw new \Exception("ReferenceDataObjectRepository::update() not implemented.");
    }
    
    /**
	 * Logically delete an existing record
	 *
	 * @param  int         $id     Entity ID of the record to delete
     * @param  AuditTrail  $audit  Audit trail object
	 * @return mixed
	 */
    public function delete($id, AuditTrail $audit = null) {
        throw new \Exception("ReferenceDataObjectRepository::delete() not implemented.");
    }
}