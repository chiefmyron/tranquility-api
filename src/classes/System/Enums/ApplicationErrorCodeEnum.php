<?php namespace Tranquility\System\Enums;

// Tranquility class libraries
use Tranquility\System\Enums\AbstractEnum as AbstractEnum;

// Tranquility application errors
use Tranquility\App\Errors\EntityNotFoundError;
use Tranquility\App\Errors\ValidationInvalidQueryParamError;
use Tranquility\App\Errors\ValidationInvalidAttributeValueError;
use Tranquility\App\Errors\RelationshipNotAllowedError;
use Tranquility\App\Errors\ValidationRelationshipInvalidObjectError;
use Tranquility\App\Errors\RelationshipInvalidEntityTypeError;
use Tranquility\App\Errors\ValidationInvalidRelationshipError;
use Tranquility\App\Errors\ValidationRelationshipInvalidTypeError;
use Tranquility\App\Errors\ValidationRelationshipNotFoundError;

/**
 * Enumeration of application message codes
 *
 * @package Tranquility\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 */
class ApplicationErrorCodeEnum extends AbstractEnum {

    // Generic validation error codes
	const ValidationMandatoryFieldMissing = '10000';
	const ValidationInvalidEmailAddress = '10001';
	const ValidationInvalidDateTimeFormat = '10002';
	const ValidationInvalidCodeValue = '10003';
	const ValidationInsufficientPasswordStrength = '10004';
	const ValidationMismatchedPasswordValues = '10005';
    const ValidationFormInputsInvalid = '10006';
    const ValidationInvalidAuditTrailUser = '10007';
    const ValidationInvalidUserCredentials = '10008';
    const ValidationUsernameInUse = '10009';
    const RecordNotFound = '10010';
    const ValidationInvalidQueryParameter = '10011';
    const ValidationInvalidAttributeValue = '10012';
    const ValidationRelationshipNotAllowed = '10013';
    const ValidationRelationshipInvalidData = '10014';
    const ValidationRelationshipInvalidEntityType = '10015';
    const ValidationRelationshipInvalid = '10016';
    const ValidationRelationshipInvalidType = '10017';
    const ValidationRelationshipNotFound = '10018';

	private static $_errorDetails = array(
        self::RecordNotFound                          => array('errorClassname' => EntityNotFoundError::class,                    'message' => 'message_10010_record_not_found'),
        self::ValidationMandatoryFieldMissing         => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10000_mandatory_field_missing'),
        self::ValidationInvalidEmailAddress           => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10001_invalid_email_address'),
        self::ValidationInvalidDateTimeFormat         => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10002_invalid_datetime_format'),
        self::ValidationInvalidCodeValue              => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10003_invalid_code_value'),
        self::ValidationInsufficientPasswordStrength  => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10004_insufficient_password_strength'),
        self::ValidationMismatchedPasswordValues      => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10005_mismatched_passwords'),
        self::ValidationInvalidUserCredentials        => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10008_invalid_user_credentials'),
        self::ValidationUsernameInUse                 => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10009_username_already_in_use'),
        self::ValidationInvalidQueryParameter         => array('errorClassname' => ValidationInvalidQueryParamError::class,       'message' => 'message_10011_invalid_query_parameter'),
        self::ValidationInvalidAttributeValue         => array('errorClassname' => ValidationInvalidAttributeValueError::class,   'message' => 'message_10012_invalid_attribute_value'),
        self::ValidationRelationshipNotAllowed        => array('errorClassname' => RelationshipNotAllowedError::class,            'message' => 'message_10013_relationship_not_allowed'),
        self::ValidationRelationshipInvalidData       => array('errorClassname' => ValidationRelationshipInvalidObjectError::class,    'message' => 'message_10014_relationship_object_is_invalid'),
        self::ValidationRelationshipInvalidEntityType => array('errorClassname' => RelationshipInvalidEntityTypeError::class,     'message' => 'message_10015_relationship_entity_type_is_invalid'),
        self::ValidationRelationshipInvalid           => array('errorClassname' => ValidationInvalidRelationshipError::class,     'message' => 'message_10016_relationship_resource_is_invalid'),
        self::ValidationRelationshipInvalidType       => array('errorClassname' => ValidationRelationshipInvalidTypeError::class, 'message' => 'message_10017_relationship_type_is_invalid'),
        self::ValidationRelationshipNotFound          => array('errorClassname' => ValidationRelationshipNotFoundError::class,    'message' => 'message_10018_relationship_not_found')
	);
	
	/**
     * Get an array containing details of the error relating to the supplied error code
     *
     * @param string $code
     * @return array
     */
    public static function getErrorDetails($code) {
		if (array_key_exists($code, self::$_errorDetails) == false) {
            throw new \Exception("Unable to find error details for code '".$code."'");
        }

        return self::$_errorDetails[$code];
    } 
}