<?php namespace Tranquility\Resources\Documents;

// Yin class libraries
use WoohooLabs\Yin\JsonApi\Document\AbstractCollectionDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApiObject;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;

// Tranquility libraries
use Tranquility\Resources\Transformers\UserTransformer;

class UserCollectionDocument extends AbstractCollectionDocument {
    
    // Constructor
    public function __construct(UserTransformer $transformer) {
        parent::__construct($transformer);
    }

    /**
     * Provides information about the "jsonapi" member of the current document.
     *
     * The method returns a new JsonApiObject schema object if this member should be present or null
     * if it should be omitted from the response.
     */

    // TODO: Replace hardcoded 1.0 with value from config
    public function getJsonApi() {
        return new JsonApiObject("1.0");
    }

    /**
     * Provides information about the "meta" member of the current document.
     *
     * The method returns an array of non-standard meta information about the document. If
     * this array is empty, the member won't appear in the response.
     */
    public function getMeta() {
        return [];
    }

    /**
     * Provides information about the "links" member of the current document.
     *
     * The method returns a new Links schema object if you want to provide linkage data
     * for the document or null if the section should be omitted from the response.
     */
    public function getLinks() {
        return Links::createWithoutBaseUri(
            [
                "self" => new Link("/users/".$this->getResourceId())
            ]
        );
    }
}