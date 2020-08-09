<?php namespace Tranquility\Documents;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Framework class libraries
use Tranquility\System\Utility;
use Tranquility\System\Enums\MessageCodeEnum;
use Tranquility\System\Exceptions\InvalidQueryParameterException;
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Documents\Components\ResourceDocumentComponent;
use Tranquility\Documents\Components\JsonApiDocumentComponent;

class ResourceDocument extends AbstractDocument {
    /**
     * Create a new document representing a single resource entity
     *
     * @param  mixed                                     $entity   The resource entity to represent
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  PSR-7 HTTP request object
     * @param  array                                     $params   [Optional] Array of additional document-specific parameters
     * @return void
     */
    public function __construct($entity, ServerRequestInterface $request, array $params = []) {
        // Make sure we are working with a single resource entity
        if ($entity instanceof AbstractEntity == false) {
            throw new \Exception("Resource document can only be populated with data from an object of class '".AbstractEntity::class."'");
        }
        
        // Set flags for this document type
        $this->isError = false;
        $this->isCollection = false;

        // Generate document member data
        $this->members['data'] = new ResourceDocumentComponent($entity, $request);
        $this->members['jsonapi'] = new JsonApiDocumentComponent($entity, $request);

        // Add in document-specific objects
        $meta = $this->_getMetaObject($entity, $request);
        if (count($meta) > 0) {
            $this->members['meta'] = $meta;
        }
        
        $links = $this->_getLinksObject($entity, $request);
        if (count($links) > 0) {
            $this->members['links'] = $links;
        }

        $included = $this->_getIncludedObject($entity, $request);
        if (count($included) > 0) {
            $this->members['included'] = $included;
        }
    }

    /**
     * Generates a meta object for the primary data represented by the document
     *
     * @param \Tranquility\Data\AbstractEntity          $entity   The data entity to build a meta object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    private function _getMetaObject(AbstractEntity $entity, ServerRequestInterface $request) {
        $meta = [];
        return $meta;
    }

    /**
     * Generates a links object for the primary data represented by the document
     *
     * @param \Tranquility\Data\AbstractEntity          $entity   The data entity to build a links object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    private function _getLinksObject(AbstractEntity $entity, ServerRequestInterface $request) {
        $links = [];
        $links['self'] = (string)$request->getUri();
        return $links;
    }

    /**
     * Generates an included object for the primary data represented by the document
     *
     * @param \Tranquility\Data\AbstractEntity          $entity   The data entity to build an included object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    private function _getIncludedObject(AbstractEntity $entity, ServerRequestInterface $request) {
        $included = [];
        
        // Check to see if the client has requested a compound document
        $queryStringParams = $request->getQueryParams();
        $include = Utility::extractValue($queryStringParams, 'include', '');
        if ($include == "") {
            return [];
        }

        // Add include for each specified entity type
        $includeTypes = explode(",", $include);
        foreach ($includeTypes as $includesPath) {
            $included = $this->_getIncludedObjectDetail($included, $includesPath, $entity, $request);
        }

        return $included;
    }

    /**
     * Gets the resource document for an included entity
     *
     * @param array                                     $included    The existing array of included entities
     * @param string                                    $entityPath  The entity path from the request that is being followed to include related entities
     * @param \Tranquility\Data\AbstractEntity          $entity      The included data entity
     * @param \Psr\Http\Message\ServerRequestInterface  $request     PSR7 request
     * @return void
     */
    private function _getIncludedObjectDetail(array $included, string $entityPath, AbstractEntity $entity, ServerRequestInterface $request) {
        // Explode out the entity name, in case it has been specified as a multi-part path
        $entityPathParts = explode('.', $entityPath, 2);
        $entityRelationships = $entity->getPublicRelationships();

        // Check that the first 'include' entity specified in the path is valid for the current parent entity
        $entityName = $entityPathParts[0];
        if (array_key_exists($entityName, $entityRelationships) == false) {
            throw new InvalidQueryParameterException((int)MessageCodeEnum::ValidationInvalidIncludedResourceType, sprintf("Resource type '%s' is not a related resource for this entity.", $entityName), 'include');
        }

        // Build a resource document for the first entity specified in the path
        $childEntity = $entity->$entityName;
        if (is_iterable($childEntity)) {
            // Child entity is a collection - add each element in the collection
            foreach ($childEntity as $child) {
                $included[] = new ResourceDocumentComponent($child, $request);
            }
        } else {
            // Child entity is a single resource - add directly
            $included[] = new ResourceDocumentComponent($childEntity, $request);
        }

        // If there are other entities specified in the remaineder of the multi-part path, continue adding them
        if (isset($entityPathParts[1]) && trim($entityPathParts[1] != '')) {
            $included = $this->_getIncludedObjectDetail($included, $entityPathParts[1], $childEntity, $request);
        }

        return $included;
    }
}