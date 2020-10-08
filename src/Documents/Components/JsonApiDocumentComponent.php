<?php namespace Tranquillity\Documents\Components;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

class JsonApiDocumentComponent extends AbstractDocumentComponent {
    /**
     * Create a new document component instance
     *
     * @param  mixed                                    $entity   The resource object or array of resource objects
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR-7 HTTP request object
     * @return void
     */
    public function __construct($entity, ServerRequestInterface $request) {
        // Start populating member data
        $this->members['version'] = "1.0";
    }
}