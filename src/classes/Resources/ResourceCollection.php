<?php namespace Tranquility\Resources;

// ORM class libraries
use Doctrine\ORM\Tools\Pagination\Paginator as Paginator;

// Class libraries
use Tranquility\System\Utility;

class ResourceCollection extends AbstractResource {

    /**
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        if (is_iterable($this->data) == false) {
            return array();
        }

        // Generate data for each resource in the array
        $collectionData = array();
        foreach ($this->data as $entity) {
            $user = new ResourceItem($entity, $this->router);
            $collectionData[] = $user;
        }

        return $collectionData;
    }

    /**
     * Generate 'meta' top-level member for response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function meta($request) {
        $meta = parent::meta($request);
        $meta['totalRecords'] = count($this->data);
        return $meta;
    }

    /**
     * Generate 'links' top-level member for response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function links($request) {
        $links = parent::links($request);

        // Check to make sure we are dealing with a paginated data set
        if (!($this->data instanceof Paginator)) {
            // Resource data is not paginated
            return $links;
        }

        // Get pagination parameters from request
        $page = $request->getQueryParam("page", array());
        $pageNumber = Utility::extractValue($page, 'number', 0);
        $pageSize = Utility::extractValue($page, 'size', 0);
        if ($pageNumber == 0 || $pageSize == 0) {
            // If there are no pagination details, response cannot be paginated
            return $links;
        }

        // Calculate pagination limits
        $totalRecordCount = $this->data->count();
        $lastPageNumber = ceil($totalRecordCount / $pageSize);

        // Get route details
        $route = $request->getAttribute('route');
        $routeName = $route->getName();

        // Generate pagination links
        $pageNumberQueryString = urlencode("page[number]");
        $pageSizeQueryString = urlencode("page[size]");
        $links['self'] = "".$request->getUri();
        $links['first'] = $request->getUri()->getBaseUrl().$this->router->pathFor($routeName)."?".$pageNumberQueryString."=1&".$pageSizeQueryString."=".$pageSize;
        $links['last'] = $request->getUri()->getBaseUrl().$this->router->pathFor($routeName)."?".$pageNumberQueryString."=".$lastPageNumber."&".$pageSizeQueryString."=".$pageSize;

        if ($pageNumber > 1) {
            $links['prev'] = $request->getUri()->getBaseUrl().$this->router->pathFor($routeName)."?".$pageNumberQueryString."=".($pageNumber - 1)."".$pageSizeQueryString."=".$pageSize;
        } else {
            $links['prev'] = null;
        }

        if ($pageNumber < $lastPageNumber) {
            $links['next'] = $request->getUri()->getBaseUrl().$this->router->pathFor($routeName)."?".$pageNumberQueryString."=".($pageNumber + 1)."&".$pageSizeQueryString."=".$pageSize;
        } else {
            $links['next'] = null;
        }

        return $links;
    }
}