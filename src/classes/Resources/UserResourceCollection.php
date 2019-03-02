<?php namespace Tranquility\Resources;

class UserResourceCollection extends AbstractResourceCollection {
    /**
     * Transform the resource into an array.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        if (is_iterable($this->data) == false) {
            return array();
        }

        // Generate data for each resource in the array
        $collectionData = array();
        foreach ($this->data as $entity) {
            $user = new UserResource($entity, $this->router);
            $collectionData[] = $user;
        }

        return $collectionData;
    }

    public function with($request) {
        $additional = array();
        
        // Add total record count to metadata
        $meta = array();
        $meta['totalRecords'] = count($this->data);
        $additional['meta'] = $meta;

        // Generate pagination links
        $links = $this->getPaginationLinks($request);
        $additional['links'] = $links;

        // Return additional data
        //return parent::additional($additional);
        return $additional;
    }
}