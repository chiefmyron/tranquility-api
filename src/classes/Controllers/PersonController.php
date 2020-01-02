<?php namespace Tranquility\Controllers;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class PersonController extends AbstractController {

    /**
     * Create a new Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_create_new_record');
        return parent::create($request, $response, $args);
    }

    /**
     * Update an existing Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_update_existing_record');
        return parent::update($request, $response, $args);
    }

    /**
     * Delete an existing Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_delete_existing_record');
        return parent::delete($request, $response, $args);
    }
}