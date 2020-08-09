<?php namespace Tranquility\Documents\Components;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Utility library classes
use Exception;
use Carbon\Carbon;

// Framework library classes
use Tranquility\Data\Entities\AbstractEntity;

class AttributesDocumentComponent extends AbstractDocumentComponent {
    /**
     * Create a new document component instance
     *
     * @param  mixed                                    $entity   The resource object or array of resource objects
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR-7 HTTP request object
     * @return void
     */
    public function __construct($entity, ServerRequestInterface $request) {
        // Check that we are working with a single entity
        if ($entity instanceof AbstractEntity == false) {
            throw new Exception("Entity provided is not an instance of '" + AbstractEntity::class + "'");
        }

        // Get attribute values and apply sparse fieldset rules
        $attributes = $this->_getAttributeValues($entity);
        $this->members = $this->_applySparseFieldset($entity->type, $attributes, $request);
    }

    private function _getAttributeValues($entity) {
        // List of top-level fields to exclude from the entity attribute list
        $excludes = array('id', 'type');
        $fields = $entity->getPublicFields();

        // Get the set of publicly available fields for the entity
        $attributes = [];
        foreach ($fields as $fieldName => $fieldDefinition) {
            if (in_array($fieldName, $excludes) == true) {
                continue; // Do not add this field to the attribute set
            }

            // Handle date values
            $value = $entity->$fieldName;
            if ($value instanceof \DateTime) {
                $value = Carbon::instance($value)->toIso8601String();
            }
            $attributes[$fieldName] = $value;
        }

        return $attributes;
    }
}