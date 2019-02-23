<?php namespace Tranquility\Resources;

class ErrorResourceCollection extends AbstractResourceCollection {
    /**
     * Transform the resource into an array.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
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