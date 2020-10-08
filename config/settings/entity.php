<?php

use Tranquillity\System\Enums\EntityTypeEnum;

return [
    EntityTypeEnum::User => [
        'controller' => Tranquillity\Controllers\UserController::class,
        'service' => Tranquillity\Services\Business\UserService::class,
        'entity' => Tranquillity\Data\Entities\Business\UserEntity::class
    ],
    EntityTypeEnum::Person => [
        'controller' => Tranquillity\Controllers\PersonController::class,
        'service' => Tranquillity\Services\Business\PersonService::class,
        'entity' => Tranquillity\Data\Entities\Business\PersonEntity::class
    ],/*,
    EntityTypeEnum::Account => [
        'controller' => Tranquillity\Controllers\AccountController::class,
        'service' => Tranquillity\Services\AccountService::class,
        'entity' => Tranquillity\Data\Entities\BusinessObjects\AccountBusinessObject::class,
        'historicalEntity' => Tranquillity\Data\Entities\HistoricalBusinessObjects\AccountHistoricalBusinessObject::class
    ],*/
    EntityTypeEnum::Tag => [
        'controller' => Tranquillity\Controllers\TagController::class,
        'service' => Tranquillity\Services\System\TagService::class,
        'entity' => Tranquillity\Data\Entities\System\TagEntity::class
    ],
    /*EntityTypeEnum::Transaction => [
        'controller' => Tranquillity\Controllers\TagController::class,
        'service' => Tranquillity\Services\TagService::class,
        'entity' => Tranquillity\Data\Entities\SystemObjects\TagSystemObject::class,
        'historicalEntity' => null
    ],*/
];