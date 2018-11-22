<?php namespace Tranquility\Transformers;

use League\Fractal\TransformerAbstract;
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;

class AbstractEntityTransformer extends TransformerAbstract {
    public function transform(BusinessObject $entity) {
        // Build attribute collection
        $attributes = array();
        $attributes['id']      = (int) $entity->id;
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