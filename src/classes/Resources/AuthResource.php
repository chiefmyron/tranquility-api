<?php namespace Tranquility\Resources;

use \Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class AuthResource extends AbstractResource {

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Include standard entity validation rules
        parent::registerValidationRules();

        // Add validation rules specific for this resource
        $this->validationRuleGroups['default'][] = array('field' => 'username', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'password', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @return string
     */
    public function getEntityClassname() {
        return User::class;
    }

    /**
     * Authenticate provided credentials and create a JWT token
     * 
     * @var  array  $data  Data used to authenticate the user
     * @return Tranquility\Data\Entities\BusinessObjects\UserBusinessObject
     */
    public function login(array $data) {
        // Get input attributes from data
        $attributes = $data['attributes'];

        // Validate input
        $validationRuleGroups = array('default');
        $result = $this->validate($attributes, $validationRuleGroups);
        if ($result !== true) {
            // Data is not valid - return error messages
            return $result;
        }

        // Retrieve user by username / email address
        $user = $this->getRepository()->findOneBy(array('username' => $attributes['username']));

        // Verify password
        if (password_verify($attributes['password'], $user->getPassword()) !== true) {
            // Invalid credentials - return error message
            $errorCode = MessageCodes::ValidationInvalidUserCredentials;
            $messageDetails = MessageCodes::getMessageDetails($errorCode);

            $errorCollection = array();
            $errorDetail = array();
            $errorDetail['source'] = ["pointer" => "/data/attributes/username"];
            $errorDetail['status'] = $messageDetails['httpStatusCode'];
            $errorDetail['code'] = $errorCode;
            $errorDetail['title'] = $messageDetails['titleMessage'];
            if ($messageDetails['detailMessage'] != '') {
                $errorDetail['detail'] = $messageDetails['detailMessage'];
            }
            $errorCollection[] = $errorDetail;
            return $errorCollection;
        }

        // Check if the user's password needs to be rehashed with a more secure algorithm
        if (password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT)) {
            // TODO: Update password here
        }

        // Create and store JSON Web Token (JWT)

    }
}