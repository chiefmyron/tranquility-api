<?php namespace Tranquillity\System\Enums;

use Tranquillity\System\Enums\AbstractEnum as AbstractEnum;

/**
 * Enumeration of query string filter parameter logical operators
 *
 * @package Tranquillity\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */

class FilterOperatorEnum extends AbstractEnum {
    const Equals           = 'eq';
    const NotEquals        = '!eq';
    const In               = 'in';
    const NotIn            = '!in';
    const IsNull           = 'null';
    const IsNotNull        = '!null';
    const Like             = 'like';
    const NotLike          = '!like';
    const GreaterThan      = 'gt';
    const GreaterThanEqual = 'gte';
    const LessThan         = 'lt';
    const LessThanEqual    = 'lte';
}