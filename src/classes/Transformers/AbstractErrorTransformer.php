<?php namespace Tranquility\Transformers;

use League\Fractal\TransformerAbstract;
use Tranquility\Data\Entities\AbstractEntity as Entity;

class AbstractTransformer extends TransformerAbstract {
    public function transform(Entity $entity) {
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