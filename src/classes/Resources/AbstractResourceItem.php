<?php namespace Tranquility\Resources;

use Carbon\Carbon;

abstract class AbstractResourceItem extends AbstractResource {

    /**
     * Generate full representation of the entity as a resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        // Format entity-specific data and attributes
        $entityData = [
            'type' => $this->data->type,
            'id' => $this->data->id,
            'attributes' => $this->getAttributes($request),
            'links' => $this->getLinks($request)
        ];

        // Add relationships to entity data
        $relationships = $this->getRelationships($request);
        if (count($relationships) > 0) {
            $entityData['relationships'] = $relationships;
        }

        // Return formatted entity
        return $entityData;
    }

    /**
     * Map entity data into a set of attributes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getAttributes($request) {
        // Always include audit trail and common entity attributes
        $attributes = [
            'version' => $this->data->version,
            'transactionId' => $this->data->audit->transactionId,
            'client' => $this->data->audit->client->clientId,
            'timestamp' => Carbon::instance($this->data->audit->timestamp)->toIso8601String(),
            'updateReason' => $this->data->audit->updateReason
        ];
        return $attributes;
    }

    /**
     * Map related entities to the main resource
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getRelationships($request) {
        $relationships = [
            'updatedByUser' => [
                'links' => [
                    'self' => $this->generateUri($request, $this->data->type.'-relationships', ['id' => $this->data->id, 'resource' => 'updatedByUser']),
                    'related' => $this->generateUri($request, $this->data->type.'-related', ['id' => $this->data->id, 'resource' => 'updatedByUser'])
                ],
                'data' => $this->_generateResourceLinkage($this->data->audit->user)
            ]
        ];

        // If a sparse fieldset has been specified, apply it before returning
        $relationships = $this->_applySparseFieldset($request, $relationships);
        return $relationships;
    }

    /**
     * Generate links related to the resource
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getLinks($request) {
        $links = [
            'self' => $this->generateUri($request, $this->data->type.'-detail', ['id' => $this->data->id])
        ];
        return $links;
    }

    /**
     * Apply sparse fieldset filters specified on the query string to the set of fields provided
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request     PSR7 request
     * @param array                                    $attributes  The set of attributes or fields that needs to be filtered
     * @return array
     */
    protected function _applySparseFieldset($request, $attributes) {
        // Get entity type
        $type = $this->data->type;
        
        // Check to see if a sparse fieldset has been applied to the entity
        $fields = $request->getQueryParam("fields", "");
        if (isset($fields[$type])) {
            $fieldNames = explode(",", $fields[$type]);
            foreach ($attributes as $attributeName => $value) {
                if (!in_array($attributeName, $fieldNames)) {
                    unset($attributes[$attributeName]);
                }
            }
        }
        return $attributes;
    }

    /**
     * Generates a resource linkage compound document from the supplied entity. If no entity is provided, null is returned.
     *
     * @param \Tranquility\Data\AbstractEntity $entity
     * @return mixed Array or null
     */
    protected function _generateResourceLinkage($entity) {
        $resourceLinkage = null;
        if (!is_null($entity)) {
            $resourceLinkage = [
                'type' => $entity->type,
                'id' => $entity->id
            ];
        }
        return $resourceLinkage;
    }
}

        