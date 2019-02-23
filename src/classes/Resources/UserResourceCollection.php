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

        $collectionData = array();
        foreach ($this->data as $entity) {
            $user = new UserResource($entity, $this->router);
            $collectionData[] = $user;
        }

        // Generate pagination links
        $links = $this->getPaginationLinks($request);

        // Add extra information to top level of response body
        $additional = array();
        $additional['meta'] = $meta;
        if (count($links) > 0) {
            $additional['links'] = $links;
        }
        $this->additional($additional);

        return $collectionData;
    }
}