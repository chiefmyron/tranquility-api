<?php namespace Tranquillity\Documents;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Vendor class libraries
use Tranquillity\App\Errors\Helpers\ErrorCollection;

// Framework class libraries
use Tranquillity\Documents\Components\JsonApiDocumentComponent;

class ErrorCollectionDocument extends AbstractDocument {
    /**
     * Create a new document representing a collection of application errors
     *
     * @param  mixed                                     $errorCollection  The collection of application errors to represent
     * @param  \Psr\Http\Message\ServerRequestInterface  $request          PSR-7 HTTP request object
     * @param  array                                     $params   [Optional] Array of additional document-specific parameters
     * @return void
     */
    public function __construct($errorCollection, ServerRequestInterface $request, array $params = []) {
        // Make sure we are working with an error collection
        if ($errorCollection instanceof ErrorCollection == false) {
            throw new \Exception("Error collection document can only be populated with data from an error collection.");
        }
        
        // Set flags for this document type
        $this->isError = true;
        $this->isCollection = true;

        // Generate document member data
        $this->members['error'] = $errorCollection->toArray();
        $this->members['meta'] = $this->_getMetaObject($errorCollection, $request);
        $this->members['jsonapi'] = new JsonApiDocumentComponent($errorCollection, $request);
    }

    /**
     * Generates a meta object for the primary data represented by the document
     *
     * @param  mixed                                    $errorCollection  The collection of application errors to build a meta object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request          PSR7 request
     * @return array
     */
    private function _getMetaObject($errorCollection, ServerRequestInterface $request) {
        $meta = [];
        $meta['totalRecords'] = count($errorCollection);
        return $meta;
    }
}