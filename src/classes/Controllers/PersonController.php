<?php namespace Tranquility\Controllers;

// Framework libraries
use Slim\Router;

// Tranquility class libraries
use Tranquility\Services\PersonService;
use Tranquility\Resources\PersonResource;
use Tranquility\Resources\PersonResourceCollection;
use Tranquility\Data\Entities\BusinessObjects\PersonBusinessObject as Person;

class PersonController extends AbstractController {

    public function __construct(PersonService $service, Router $router) {
        // Set the resources used to represent People entities
        $this->entityClassname = Person::class;
        $this->entityResourceClassname = PersonResource::class;
        $this->entityResourceCollectionClassname = PersonResourceCollection::class;
        return parent::__construct($service, $router);
    }

    public function create($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_create_new_record');
        return parent::create($request, $response, $args);
    }

    public function update($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_update_existing_record');
        return parent::update($request, $response, $args);
    }

    public function delete($request, $response, $args) {
        // Update request with audit trail information in the 'meta' section
        $request = $this->_setAuditTrailReason($request, 'person_delete_existing_record');
        return parent::delete($request, $response, $args);
    }
}