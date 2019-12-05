<?php namespace Tranquility\Resources;

// Utility libraries
use Carbon\Carbon;

// Tranquility class libraries
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

class ResourceItem extends AbstractResource {

    /**
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
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
     * Generate 'included' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function included($request) {
        $included = parent::included($request);

        // Check to see if the client has requested a compound document
        $include = trim($request->getQueryParam("include", ""));
        if ($include == "") {
            return $included;
        }

        // Add include for each specified entity type
        $includeTypes = explode(",", $include);
        foreach ($includeTypes as $includesPath) {
            $included = $this->getIncludeDetail($included, $includesPath, $this->data, $request);
        }

        return $included;
    }

    /**
     * Map entity data into a set of attributes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function getAttributes($request) {
        // List of top-level fields to exclude from the entity attribute list
        $excludes = array('id', 'type');
        $fields = $this->data->getPublicFields();

        // Get the set of publicly available fields for the entity
        $attributes = array();
        foreach ($fields as $field) {
            if (in_array($field, $excludes) == true) {
                continue; // Do not add this field to the attribute set
                
            }

            // Handle date values
            $value = $this->data->$field;
            if ($value instanceof \DateTime) {
                $value = Carbon::instance($value)->toIso8601String();
            }
            $attributes[$field] = $value;
        }

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
        // Get the set of publicly available related entities and entity collections for the entity
        $relations = $this->data->getPublicRelationships();

        $relationships = array();
        foreach ($relations as $name => $relation) {
            $relationships[$name] = [
                'links' => [
                    'self' => $this->generateUri($request, $this->data->type.'-relationships', ['id' => $this->data->id, 'resource' => $name]),
                    'related' => $this->generateUri($request, $this->data->type.'-related', ['id' => $this->data->id, 'resource' => $name])
                ],
                'data' => $this->_generateResourceLinkage($this->data->$name, $relation['relationshipType'])
            ];
        }

        // If a sparse fieldset has been specified, apply it before returning
        $relationships = $this->_applySparseFieldset($request, $relationships);
        return $relationships;
    }

    public function getIncludeDetail($includes, $entityPath, $entity, $request) {
        // Explode out the entity name, in case it has been specified as a multi-part path
        $entityPathParts = explode(".", $entityPath, 2);

        // Build a resource document for the first entity specified in the path
        $entityName = $entityPathParts[0];
        $childEntity = $entity->$entityName;
        $resource = new ResourceItem($childEntity, $this->router);
        $includes[] = $resource->data($request);

        // If there are other entities specified in the remaineder of the multi-part path, continue adding them
        if (isset($entityPathParts[1]) && trim($entityPathParts[1] != "")) {
            $includes = $this->getIncludeDetail($includes, $entityPathParts[1], $childEntity, $request);
        }

        return $includes;
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
     * @param string                           $relationshipType
     * @return mixed Array or null
     */
    protected function _generateResourceLinkage($entity, $relationshipType) {
        $resourceLinkage = null;

        // Linkage document format will depend on the relationship type
        if ($relationshipType == EntityRelationshipTypeEnum::Single) {
            if (!is_null($entity)) {
                $resourceLinkage = [
                    'type' => $entity->type,
                    'id' => $entity->id
                ];
            }
        } elseif ($relationshipType == EntityRelationshipTypeEnum::Collection) {
            $resourceLinkage = [];

            if (is_iterable($entity) && count($entity) > 0) {
                foreach($entity as $item) {
                    $resourceLinkage[] = [
                        'type' => $item->type,
                        'id' => $item->id
                    ];
                }
            }
        }

        return $resourceLinkage;
    }
}

        