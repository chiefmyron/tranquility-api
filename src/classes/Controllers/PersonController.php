<?php namespace Tranquility\Controllers;

class PersonController extends AbstractController {

    /**
     * Create a new Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_create_new_record');
        return parent::create($request, $response, $args);
    }

    /**
     * Update an existing Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_update_existing_record');
        return parent::update($request, $response, $args);
    }

    /**
     * Delete an existing Person entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_delete_existing_record');
        return parent::delete($request, $response, $args);
    }
}