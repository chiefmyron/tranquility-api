<?php namespace Tranquility\Resources;

// Tranquility class libraries
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class ErrorNotFoundResource extends ErrorResource {

    /**
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        $messageCode = MessageCodes::RecordNotFound;
        $messageDetails = MessageCodes::getMessageDetails($messageCode);

        // Build JSON API compliant error
        $errorDetail = [
            'id'     => $this->data,
            'status' => $messageDetails['httpStatusCode'],
            'code'   => $messageCode,
            'title'  => $messageDetails['titleMessage']
        ];

        // If the message has detail text, add it as well
        if ($messageDetails['detailMessage'] != '') {
            $errorDetail['detail'] = $messageDetails['detailMessage'];
        }

        // Add to error array and return
        $errorCollection = array();
        $errorCollection[] = $errorDetail;

        return $errorCollection;
    }
}