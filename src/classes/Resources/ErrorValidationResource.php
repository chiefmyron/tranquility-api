<?php namespace Tranquility\Resources;

// Tranquility class libraries
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class ErrorValidationResource extends ErrorResource {

    /**
     * Generate full representation of the error set as a resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        $errorCollection = array();
        foreach ($this->data as $field => $messages) {
            foreach ($messages as $code) {
                // Get message details for error code
                $messageDetails = MessageCodes::getMessageDetails($code);

                // Build JSON API compliant error
                $errorDetail = [
                    'source' => ["pointer" => "/data/attributes/".$field],
                    'status' => $messageDetails['httpStatusCode'],
                    'code'   => $code,
                    'title'  => $messageDetails['titleMessage']
                ];

                // If the message has detail text, add it as well
                if ($messageDetails['detailMessage'] != '') {
                    $errorDetail['detail'] = $messageDetails['detailMessage'];
                }

                // Add error to collection
                $errorCollection[] = $errorDetail;
            }
        }

        return $errorCollection;
    }
}