<?php namespace Tranquillity\Documents;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;

// Vendor class libraries
use Doctrine\ORM\Tools\Pagination\Paginator as Paginator;

// Framework class libraries
use Tranquillity\System\Utility;
use Tranquillity\Documents\Components\ResourceDocumentComponent;
use Tranquillity\Documents\Components\JsonApiDocumentComponent;

class ResourceCollectionDocument extends AbstractDocument {
    /**
     * Create a new document representing a collection of resource entities
     *
     * @param  mixed                                     $entityCollection   The collection of resource entities to represent
     * @param  \Psr\Http\Message\ServerRequestInterface  $request            PSR-7 HTTP request object
     * @param  array                                     $params             [Optional] Array of additional document-specific parameters
     * @return void
     */
    public function __construct($entityCollection, ServerRequestInterface $request, array $params = []) {
        // Make sure we are working with an iterable collection of entities
        if (is_iterable($entityCollection) == false) {
            throw new \Exception("Resource collection document can only be populated with data from an iterable object collection.");
        }
        
        // Set flags for this document type
        $this->isError = false;
        $this->isCollection = true;

        // Generate document member data
        $this->members['data'] = $this->_getDataObject($entityCollection, $request);
        $this->members['jsonapi'] = new JsonApiDocumentComponent($entityCollection, $request);

        // Add in document-specific objects
        $meta = $this->_getMetaObject($entityCollection, $request);
        if (count($meta) > 0) {
            $this->members['meta'] = $meta;
        }
        
        $links = $this->_getLinksObject($entityCollection, $request);
        if (count($links) > 0) {
            $this->members['links'] = $links;
        }
    }

    private function _getDataObject($entityCollection, ServerRequestInterface $request) {
        $data = [];
        foreach ($entityCollection as $entity) {
            $data[] = new ResourceDocumentComponent($entity, $request);
        }
        return $data;
    }

    /**
     * Generates a meta object for the primary data represented by the document
     *
     * @param  mixed                                    $entityCollection  The collection of resource entities to build a meta object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request           PSR7 request
     * @return array
     */
    private function _getMetaObject($entityCollection, ServerRequestInterface $request) {
        $meta = [];
        $meta['totalRecords'] = count($entityCollection);
        return $meta;
    }

    /**
     * Generates a links object for the primary data represented by the document
     *
     * @param  mixed                                    $entityCollection  The collection of resource entities to build a links object for
     * @param \Psr\Http\Message\ServerRequestInterface  $request           PSR7 request
     * @return array
     */
    private function _getLinksObject($entityCollection, ServerRequestInterface $request) {
        $links = [];

        // Check to make sure we are dealing with a paginated data set
        if (!($entityCollection instanceof Paginator)) {
            // Resource data is not paginated
            return $links;
        }

        // Get pagination parameters from request
        $requestParams = $request->getQueryParams();
        $page = Utility::extractValue($requestParams, 'page', array());
        $pageNumber = Utility::extractValue($page, 'number', 0);
        $pageSize = Utility::extractValue($page, 'size', 0);
        if ($pageNumber == 0 || $pageSize == 0) {
            // If there are no pagination details, response cannot be paginated
            return $links;
        }

        // Calculate pagination limits
        $totalRecordCount = $entityCollection->count();
        $lastPageNumber = ceil($totalRecordCount / $pageSize);

        // Get route details
        $route = $request->getAttribute('route');
        $routeName = $route->getName();
        $routeArgs = $route->getArguments();

        // Generate pagination links
        $links['self'] = "".$request->getUri();
        $links['first'] = Utility::getRouteUrl($request, $routeName, $routeArgs, ['page[number]' => '1', 'page[size]' => $pageSize]);
        $links['last'] = Utility::getRouteUrl($request, $routeName, $routeArgs, ['page[number]' => $lastPageNumber, 'page[size]' => $pageSize]);

        if ($pageNumber > 1) {
            $links['prev'] = Utility::getRouteUrl($request, $routeName, $routeArgs, ['page[number]' => ($pageNumber - 1), 'page[size]' => $pageSize]);
        } else {
            $links['prev'] = null;
        }

        if ($pageNumber < $lastPageNumber) {
            $links['next'] = Utility::getRouteUrl($request, $routeName, $routeArgs, ['page[number]' => ($pageNumber + 1), 'page[size]' => $pageSize]);
        } else {
            $links['next'] = null;
        }

        return $links;
    }
}