<?php namespace Tranquillity\Documents\Components;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Utility library classes
use Exception;

// Framework library classes
use Tranquillity\System\Utility;
use Tranquillity\Data\Entities\AbstractEntity;

class ResourceDocumentComponent extends AbstractDocumentComponent {
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
            throw new Exception("Entity provided ('" . get_class($entity) . "') is not an instance of '" . AbstractEntity::class . "'");
        }

        // Start populating member data
        $this->members['id'] = $entity->id;
        $this->members['type'] = $entity->type;
        $this->members['attributes'] = new AttributesDocumentComponent($entity, $request);
        $this->members['relationships'] = new RelationshipsDocumentComponent($entity, $request);
        $this->members['links'] = $this->_getLinks($entity, $request);
    }

    /**
     * Generates a links object for the current resource object
     *
     * @param \Tranquillity\Data\AbstractEntity          $entity   The data entity to build a resource linkage object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    private function _getLinks(AbstractEntity $entity, ServerRequestInterface $request) {
        $links = [];
        $links['self'] = Utility::getRouteUrl($request, $entity->type.'-detail', ['id' => $entity->id]);
        return $links;
    }
}