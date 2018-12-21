<?php namespace Tranquility\Resources\Transformers;

// Yin class libraries
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;

class UserResourceTransformer extends AbstractResourceTransformer {
    
    // Constructor
    public function __construct() {

    }

    /**
     * Provides information about the "type" member of the current resource.
     *
     * The method returns the type of the current resource.
     *
     * @param array $user
     */
    // TODO: Replace hardcoded 'users' based on entity type
    public function getType($user) {
        return "users";
    }

    /**
     * Provides information about the "id" member of the current resource.
     *
     * @param array $user
     */
    public function getId($user) {
        return $user['id'];
    }

    
}