<?php

namespace TitaKita\Resources\Webhook;

use TitaKita\DomainObjects\WebhookDomainObject;

/**
 * @mixin WebhookDomainObject
 */
class WebhookResourceWithSecret extends WebhookResource
{
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'secret' => $this->getSecret(),
        ]);
    }
}
