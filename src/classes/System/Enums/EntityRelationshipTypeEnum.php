<?php namespace Tranquility\System\Enums;

use Tranquility\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of entity relationship types
 *
 * @package Tranquility\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class EntityRelationshipTypeEnum extends AbstractEnum {
	const Single     = 'single';
	const Collection = 'collection';
}