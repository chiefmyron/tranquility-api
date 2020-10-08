<?php namespace Tranquillity\System\Enums;

use Tranquillity\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of entity relationship types
 *
 * @package Tranquillity\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class EntityRelationshipTypeEnum extends AbstractEnum {
	const Single     = 'single';
	const Collection = 'collection';
}