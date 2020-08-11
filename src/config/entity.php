<?php

use Tranquility\System\Enums\EntityTypeEnum;

return [
    EntityTypeEnum::User => [
        'controller' => Tranquility\Controllers\UserController::class,
        'service' => Tranquility\Services\Business\UserService::class,
        'entity' => Tranquility\Data\Entities\Business\UserEntity::class
    ],
    EntityTypeEnum::Person => [
        'controller' => Tranquility\Controllers\PersonController::class,
        'service' => Tranquility\Services\Business\PersonService::class,
        'entity' => Tranquility\Data\Entities\Business\PersonEntity::class
    ],/*,
    EntityTypeEnum::Account => [
        'controller' => Tranquility\Controllers\AccountController::class,
        'service' => Tranquility\Services\AccountService::class,
        'entity' => Tranquility\Data\Entities\BusinessObjects\AccountBusinessObject::class,
        'historicalEntity' => Tranquility\Data\Entities\HistoricalBusinessObjects\AccountHistoricalBusinessObject::class
    ],*/
    EntityTypeEnum::Tag => [
        'controller' => Tranquility\Controllers\TagController::class,
        'service' => Tranquility\Services\System\TagService::class,
        'entity' => Tranquility\Data\Entities\System\TagEntity::class
    ],
    /*EntityTypeEnum::Transaction => [
        'controller' => Tranquility\Controllers\TagController::class,
        'service' => Tranquility\Services\TagService::class,
        'entity' => Tranquility\Data\Entities\SystemObjects\TagSystemObject::class,
        'historicalEntity' => null
    ],*/
];