<?php namespace Tranquility\Transformers;

use Tranquility\Data\Entities\UserEntity as User;
use Tranquility\Data\Entities\AbstractEntity as Entity;
use Tranquility\Transformers\AbstractEntityTransformer;

class UserTransformer extends AbstractEntityTransformer {
    
    public function transform(Entity $user) {
        // Make sure that a User entity has been provided
        if (!($user instanceof User)) {
            throw new \Exception("The supplied object must be an instance of UserEntity to be used with this transformer.");
        }

        // Get standard schema for entities
        $entitySchema = parent::transform($user);

        // Define additional schema entries for User entity
        $userSchema = array (
            'username'           => $user->username,
            'timezoneCode'       => $user->timezoneCode,
            'localeCode'         => $user->localeCode,
            'active'             => (bool) $user->active,
            'securityGroupId'    => (int) $user->securityGroupId,
            'registeredDateTime' => $user->registeredDateTime,
            'links' => array(
                array(
                    'rel' => 'self',
                    'uri' => '/users/'.$user->id
                )
            ),
        );

        // Return unified schema
        return array_merge($entitySchema, $userSchema);
    }
}