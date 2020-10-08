<?php namespace Tranquillity\Documents\Components;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Utility library classes
use Exception;

// Framework library classes
use Tranquillity\Data\Entities\AbstractEntity;

class ResourceIdentifierDocumentComponent extends AbstractDocumentComponent {
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

        // Start populating member data
        $this->members['id'] = $entity->id;
        $this->members['type'] = $entity->type;
    }
}