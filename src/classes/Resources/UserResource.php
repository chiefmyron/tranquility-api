<?php namespace Tranquility\Resources;

class UserResource extends AbstractResourceItem {
    /**
     * Transform the resource into an array.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        // Get standard audit trail data
        $auditData = $this->getAuditTrailArray($request);

        // Format entity-specific data
        $entityData = [
            'attributes' => [
                'username' => $this->data->username,
                'timezoneCode' => $this->data->timezoneCode,
                'localeCode' => $this->data->localeCode,
                'active' => $this->data->active,
                'securityGroupId' => $this->data->securityGroupId,
                'registeredDateTime' => $this->data->registeredDateTime
            ],
            'links' => [
                'self' => $request->getUri()->getBaseUrl().$this->router->pathFor('users-detail', ['id' => $this->data->id])
            ]
        ];

        // Combine entity and audit data and return
        return array_merge_recursive($auditData, $entityData);
    }
}