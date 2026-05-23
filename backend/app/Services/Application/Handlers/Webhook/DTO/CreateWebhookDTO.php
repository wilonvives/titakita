<?php

namespace TitaKita\Services\Application\Handlers\Webhook\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\Status\WebhookStatus;

class CreateWebhookDTO extends BaseDTO
{
    public function __construct(
        public string        $url,
        public array         $eventTypes,
        public int           $userId,
        public int           $accountId,
        public WebhookStatus $status,
        public ?int          $eventId = null,
        public ?int          $organizerId = null,
    )
    {
    }
}
