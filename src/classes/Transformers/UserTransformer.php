<?php namespace Tranquility\Transformers;

use Tranquility\Transformers\AbstractEntityTransformer;
use Tranquility\Data\Entities\BusinessObjects\AbstractBusinessObject as BusinessObject;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class UserTransformer extends AbstractEntityTransformer {
    
    public function transform(BusinessObject $user) {
        // Make sure that a User entity has been provided
        if (!($user instanceof User)) {
            throw new \Exception("The supplied object must be an instance of ".User::class." to be used with this transformer.");
        }

        // Get standard schema for entities
        $entitySchema = parent::transform($user);

        // Define additional schema entries for User entity
        $attributes = array (
            'username'           => $user->username,
            'timezoneCode'       => $user->timezoneCode,
            'localeCode'         => $user->localeCode,
            'active'             => (bool) $user->active,
            'securityGroupId'    => (int) $user->securityGroupId,
            'registeredDateTime' => $user->registeredDateTime
        );
        $entitySchema = array_merge($entitySchema, $attributes);

        // Add links for User entity
        //$entitySchema['links']['self'] = '/users/'.$user->id;

        // Return unified schema
        return $entitySchema;
    }
}