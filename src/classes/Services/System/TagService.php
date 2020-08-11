<?php namespace Tranquility\Services\System;

// Vendor class libraries
use Valitron\Validator;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Framework class libraries
use Tranquility\Data\Entities\System\TagEntity as Tag;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class TagService extends AbstractSystemService {

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $em         ORM entity manager
     * @param  \Valitron\Validator                   $validator  Validation engine 
     * @return void
     */
    public function __construct(EntityManagerInterface $em, Validator $validator) {
        parent::__construct($em, $validator);

        // Set the entity classname for this service
        $this->entityClassname = Tag::class;
    }

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Common validation rules for a Tag entity
        $this->validationRuleGroups['default'][] = array('field' => 'text', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
    }
}