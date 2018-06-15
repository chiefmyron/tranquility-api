<?php namespace Tranquility\System\Enums;

use Tranquility\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of entity types
 *
 * @package Tranquility\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class EntityTypeEnum extends AbstractEnum {
	const Person  = 'person';
	const Content = 'content';
	const User    = 'user';
	const Account = 'account';
	const Contact = 'contact';
    const Address = 'address';
    const AddressPhysical = 'addressPhysical';
}