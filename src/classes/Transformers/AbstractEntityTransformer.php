<?php namespace Tranquility\Transformers;

use League\Fractal\TransformerAbstract;
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;

class AbstractEntityTransformer extends TransformerAbstract {
    public function transform(BusinessObject $entity) {
        return array(
            'id'      => (int) $entity->id,
	        'version' => (int) $entity->version,
            'type'    => $entity->type,
            'subType' => $entity->subType,
            'deleted' => (bool) $entity->deleted,
            'locks'   => $entity->locks
        );
    }
}