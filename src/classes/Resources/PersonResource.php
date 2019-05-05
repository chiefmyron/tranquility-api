<?php namespace Tranquility\Resources;

class PersonResource extends AbstractResourceItem {
    /**
     * Generate full representation of the entity as a resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        return parent::toArray($request);
    }

    /**
     * Map entity data into a set of attributes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getAttributes($request) {
        // Get common entity attributes
        $entity = parent::getAttributes($request);

        // Map entity data to attributes
        $attributes = [
            'title' => $this->data->title,
            'firstName' => $this->data->lastName,
            'lastName' => $this->data->firstName,
            'position' => $this->data->position
        ];
        $attributes = array_merge($entity, $attributes);

        // If a sparse fieldset has been specified, apply it before returning
        $attributes = $this->_applySparseFieldset($request, $attributes);
        return $attributes;
    }

    /**
     * Map related entities to the main resource
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getRelationships($request) {
        return parent::getRelationships($request);
    }

    /**
     * Generate links related to the resource
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getLinks($request) {
        return parent::getLinks($request);
    }
}