<?php namespace Tranquillity\Documents\Components;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Utility library classes
use Exception;

// Framework library classes
use Tranquillity\System\Utility;
use Tranquillity\Data\Entities\AbstractEntity;
use Tranquillity\System\Enums\EntityRelationshipTypeEnum;

class RelationshipsDocumentComponent extends AbstractDocumentComponent {
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

        // Get relationship objects and apply sparse fieldset rules
        $relationships = $this->_getRelationshipObjects($entity, $request);
        $this->members = $this->_applySparseFieldset($entity->type, $relationships, $request);
    }

    /**
     * Map related entities to the main resource
     *
     * @param \Tranquillity\Data\AbstractEntity          $entity   The data entity to get relationships from
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    private function _getRelationshipObjects(AbstractEntity $entity, ServerRequestInterface $request) {
        // Get the set of publicly available related entities and entity collections for the entity
        $relations = $entity->getPublicRelationships();

        $relationships = array();
        foreach ($relations as $name => $definition) {
            $relationships[$name] = [
                'links' => [
                    'self' => Utility::getRouteUrl($request, $entity->type.'-relationships', ['id' => $entity->id, 'resource' => $name]),
                    'related' => Utility::getRouteUrl($request, $entity->type.'-related', ['id' => $entity->id, 'resource' => $name])
                ],
                'data' => $this->_getResourceLinkageObject($entity->$name, $definition['collection'], $request)
            ];
        }
        return $relationships;
    }

    /**
     * Generates a resource linkage compound document from the supplied entity. If no entity is provided, null is returned.
     *
     * @param mixed                  $entity      The data entity (or entity collection) to build a resource linkage object for
     * @param boolean                $collection  True if the linked resource is a collection, otherwise false
     * @param ServerRequestInterface $request     PSR7 request
     * @return void
     */
    private function _getResourceLinkageObject($entity, bool $collection, ServerRequestInterface $request) {
        $resourceLinkage = null;

        // Linkage document format will depend on the relationship type
        if ($collection == true) {
            $resourceLinkage = [];
            if (is_iterable($entity) && count($entity) > 0) {
                foreach($entity as $item) {
                    $resourceLinkage[] = new ResourceIdentifierDocumentComponent($item, $request);
                }
            }
        } else {
            // Single re
            if (!is_null($entity)) {
                $resourceLinkage = new ResourceIdentifierDocumentComponent($entity, $request);
            }
        }

        return $resourceLinkage;
    }
}