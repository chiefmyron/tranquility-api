<?php namespace Tranquility\Resources;

use Carbon\Carbon;

abstract class AbstractResourceItem extends AbstractResource {

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

    public abstract function getRelationships($request);

    public abstract function getLinks($request);

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
}

        