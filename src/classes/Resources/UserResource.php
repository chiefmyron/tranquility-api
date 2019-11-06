<?php namespace Tranquility\Resources;

class UserResource extends AbstractResourceItem {
    
    /**
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        return parent::data($request);
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
            'username' => $this->data->username,
            'timezoneCode' => $this->data->timezoneCode,
            'localeCode' => $this->data->localeCode,
            'active' => $this->data->active,
            'securityGroupId' => $this->data->securityGroupId,
            'registeredDateTime' => $this->data->registeredDateTime
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