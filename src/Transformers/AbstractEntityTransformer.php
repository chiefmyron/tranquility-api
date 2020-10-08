<?php namespace Tranquillity\Transformers;

use League\Fractal\TransformerAbstract;
use Tranquillity\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;

class AbstractEntityTransformer extends TransformerAbstract {
    public function transform(BusinessObject $entity) {
        // Build attribute collection
        $attributes = array();
        $attributes['id']      = (int) $entity->id;
        $attributes['type']    = $this->type;
        $attributes['version'] = (int) $entity->version;
        $attributes['deleted'] = (bool) $entity->deleted;
        $attributes['locks']   = $entity->locks;
        if (!is_null($entity->subType)) {
            $attributes['subType'] = $entity->subType;
        }

        // Build overall structure
        /*$root = array();
        $root['id'] = (int) $entity->id;
        $root['type'] = $entity->type;
        $root['attributes'] = $attributes;
        $root['relationships'] = array();
        $root['links'] = array();
        $root['meta'] = array();*/
        return $attributes;
    }
}