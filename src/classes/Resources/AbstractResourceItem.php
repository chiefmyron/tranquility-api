<?php namespace Tranquility\Resources;

use Carbon\Carbon;

abstract class AbstractResourceItem extends AbstractResource {

    protected function getAuditTrailArray($request) {

        return [
            'type' => $this->data->type,
            'id' => $this->data->id,
            'attributes' => [
                'version' => $this->data->version,
                'transactionId' => $this->data->audit->transactionId,
                'client' => $this->data->audit->client->clientId,
                'timestamp' => Carbon::instance($this->data->audit->timestamp)->toIso8601String(),
                'updateReason' => $this->data->audit->updateReason
            ],
            'relationships' => [
                'updatedByUser' => [
                    'links' => [
                        'self' => '',
                        'related' => $request->getUri()->getBaseUrl().$this->router->pathFor('users-detail', ['id' => $this->data->id])
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

        