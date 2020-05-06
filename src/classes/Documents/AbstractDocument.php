<?php namespace Tranquility\Documents;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Vendor class libraries
use Carbon\Carbon;

// Framework class libraries
use Tranquility\App\Errors\Helpers\ErrorCollection;
use Tranquility\System\Enums\DocumentTypeEnum;
use Tranquility\Documents\Components\AbstractDocumentComponent;

abstract class AbstractDocument {
    /**
     * Top-level members of the resource document
     *
     * @var array
     */
    protected $members = [];

    /**
     * Flag to indicate whether the resource represents a collection of data
     *
     * @var boolean
     */
    protected $isCollection = false;

    /**
     * Flag to indicate whether the resource represents one or more errors
     *
     * @var boolean
     */
    protected $isError = false;

    /**
     * Create a new document instance.
     *
     * @param  mixed                                     $entity   The resource object or array of resource objects
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  PSR-7 HTTP request object
     * @param  array                                     $params   [Optional] Array of additional document-specific parameters
     * @return void
     */
    public abstract function __construct($entity, ServerRequestInterface $request, array $params = []);

    /**
     * Creates the appropriate concrete version of an AbstractDocument, based on the entity data provided
     *
     * @param  mixed                                     $entity        The resource object or array of resource objects
     * @param  \Psr\Http\Message\ServerRequestInterface  $request       PSR-7 HTTP request object
     * @param  string                                    $documentType  [Optional] Force the document to generate for the supplied entity
     * @return AbstractDocument
     */
    public static function createDocument($entity, ServerRequestInterface $request, string $documentType = '') {
        // Determine document type from supplied entity
        if ($documentType == '') {
            if ($entity instanceof ErrorCollection) {
                $documentType = DocumentTypeEnum::ErrorCollection;
            } elseif (is_array($entity) || is_iterable($entity)) {
                $documentType = DocumentTypeEnum::ResourceCollection;
            } elseif (is_null($entity) == false) {
                $documentType = DocumentTypeEnum::Resource;
            }
        }

        // Generate document using the selected document type
        $documentClassname = DocumentTypeEnum::getDocumentClassname($documentType);
        return new $documentClassname($entity, $request);
    }

    /**
     * Shows whether the document represents a collection of entries
     *
     * @return boolean
     */
    function isCollection() {
        return $this->isCollection;
    }

    /**
     * Shows whether the document represents an error or collection of errors
     *
     * @return boolean
     */
    function isError() {
        return $this->isError;
    }

    /**
     * Check if the top-level member of the document exists
     *
     * @param string $memberName
     * @return bool
     */
    public function hasMember(string $memberName) {
        if (!array_key_exists($memberName, $this->members)) {
            return false;
        }

        return true;
    }

    /**
     * Get data from a member of the document
     *
     * @param string  $memberName  Name of the document member to retrieve data for
     * @param bool    $asArray     If true, returns all data underneath the member in an array
     * @return mixed
     */
    public function getMember(string $memberName, bool $asArray = false) {
        if (!array_key_exists($memberName, $this->members)) {
            return null;
        }

        $member = $this->members[$memberName];
        if ($asArray == true) {
            return $this->_resolveDocumentMembers($member);
        }
        return $member;
    }

    /**
     * Converts the document into an array
     *
     * @return array
     */
    public function toArray() {
        return $this->_resolveDocumentMembers($this->members);
    }

    /**
     * Iterates through an array representation of document members and recursively 
     * processes any related document components.
     *
     * @param array $data
     * @return array
     */
    private function _resolveDocumentMembers($data) {
        // Recursively resolve embedded resources and correctly format values
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // If value is an array, resolve all elements of the array
                $data[$key] = $this->_resolveDocumentMembers($value);
            } elseif ($value instanceof \DateTime) {
                // If value is a DateTime value, convert to ISO8601 valid string
                $data[$key] = Carbon::instance($value)->toIso8601String();
            } elseif ($value instanceof AbstractDocumentComponent) {
                // If value is a document component, convert it to an array and then resolve all elements of that array
                $documentComponent = $value->toArray();
                $data[$key] = $this->_resolveDocumentMembers($documentComponent);
            }
        }

        return $data;
    }
}