<?php namespace Tranquility\Resources;

abstract class AbstractResourceItem extends AbstractResource {

    protected function getAuditTrailArray($request) {
        // Get router to generate URIs
        $router = $request->getAttribute('router');

        return [
            'type' => $this->data->type,
            'id' => $this->data->id,
            'attributes' => [
                'version' => $this->data->version,
                'transactionId' => $this->data->audit->transactionId,
                'client' => $this->data->audit->client->clientId,
                'timestamp' => $this->data->audit->timestamp,
                'updateReason' => $this->data->audit->updateReason
            ],
            'relationships' => [
                'updatedByUser' => [
                    'links' => [
                        'self' => '',
                        //'related' => $request->getUri()->getBaseUrl().$router->pathFor('users-detail', ['id' => $this->data->id])
                    ],
                    'data' => [
                        'type' => $this->data->audit->user->type,
                        'id' => $this->data->audit->user->id
                    ]
                ]
            ]
        ];
    }
}

        