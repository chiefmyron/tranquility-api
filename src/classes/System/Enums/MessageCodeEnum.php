<?php namespace Tranquility\System\Enums;

use Tranquility\System\Enums\AbstractEnum as AbstractEnum;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Enumeration of application message codes
 *
 * @package Tranquility\System\Enums
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://httpstatuses.com/
 */

class MessageCodeEnum extends AbstractEnum {

	// Generic validation error codes
	const ValidationMandatoryFieldMissing = '10000';
	const ValidationInvalidEmailAddress = '10001';
	const ValidationInvalidDateTimeFormat = '10002';
	const ValidationInvalidTransactionSource = '10003';
	const ValidationInsufficientPasswordStrength = '10004';
	const ValidationMismatchedPasswordValues = '10005';
    const ValidationFormInputsInvalid = '10006';
    const ValidationInvalidAuditTrailUser = '10007';
    const ValidationInvalidUserCredentials = '10008';

	private static $_messageDetails = array(
        // Generic form validation messages
        self::ValidationMandatoryFieldMissing => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10000_mandatory_field_missing', 'detailMessage' => ''),
        self::ValidationInvalidEmailAddress => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10001_invalid_email_address', 'detailMessage' => ''),
        self::ValidationInvalidDateTimeFormat => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10002_invalid_datetime_format', 'detailMessage' => ''),
        //self::ValidationInvalidTransactionSource => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10003_invalid_transaction_source', 'detailMessage' => ''),  // This scenario is covered by using the OAuth Client ID as the transaction source
        self::ValidationInsufficientPasswordStrength => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10004_insufficient_password_strength', 'detailMessage' => ''),
        //self::ValidationMismatchedPasswordValues => array('type' => 'field',  'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10005_mismatched_passwords', 'detailMessage' => ''),  // This should be done by the client
        self::ValidationFormInputsInvalid => array('type' => 'header', 'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10006_form_validation_failed', 'detailMessage' => ''),
        self::ValidationInvalidAuditTrailUser => array('type' => 'field', 'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10007_invalid_update_by_user', 'detailMessage' => ''),
        self::ValidationInvalidUserCredentials => array('type' => 'field', 'level' => 'error', 'httpStatusCode' => HttpStatus::UnprocessableEntity, 'titleMessage' => 'message_10008_invalid_user_credentials', 'detailMessage' => '')
	);
	
	/**
     * Get an array containing details of the error relating to the supplied error code
     *
     * @param string $code
     * @param array $fieldValues
     * @return array
     */
    public static function getMessageDetails($code, $fieldValues = array()) {
		if (array_key_exists($code, self::$_messageDetails) == false) {
            throw new \Exception("Unable to find message details for code '".$code."'");
        }

        $messageDetails = self::$_messageDetails[$code];
        return $messageDetails;
    } 
}