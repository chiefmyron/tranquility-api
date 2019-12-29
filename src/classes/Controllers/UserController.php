<?php namespace Tranquility\Controllers;

class UserController extends AbstractController {

    public function create($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'user_create_new_record');
        return parent::create($request, $response, $args);
    }

    public function update($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'user_update_existing_record');
        return parent::update($request, $response, $args);
    }

    public function delete($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'user_delete_existing_record');
        return parent::delete($request, $response, $args);
    }
}