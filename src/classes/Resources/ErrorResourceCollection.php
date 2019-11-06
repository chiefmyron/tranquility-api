<?php namespace Tranquility\Resources;

class ErrorResourceCollection extends AbstractResourceCollection {
    
    /**
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        if (is_array($this->data) == false) {
            return array();
        }

        $collectionData = array();
        foreach ($this->data as $entity) {
            $user = new UserResource($entity, $this->router);
            $collectionData[] = $user;
        }

        return $collectionData;
    }
}