<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Message;

use TitaKita\DomainObjects\MessageDomainObject;
use TitaKita\DomainObjects\Status\MessageStatus;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CancelMessageHandler
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
    )
    {
    }

    public function handle(int $messageId, int $eventId): MessageDomainObject
    {
        $message = $this->messageRepository->findFirstWhere([
            'id' => $messageId,
            'event_id' => $eventId,
        ]);

        if ($message === null) {
            throw new ResourceNotFoundException(__('Message not found'));
        }

        if ($message->getStatus() !== MessageStatus::SCHEDULED->name) {
            throw ValidationException::withMessages([
                'status' => [__('Only scheduled messages can be cancelled')],
            ]);
        }

        $updated = $this->messageRepository->updateWhere(
            ['status' => MessageStatus::CANCELLED->name],
            ['id' => $messageId, 'status' => MessageStatus::SCHEDULED->name],
        );

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'status' => [__('This message can no longer be cancelled')],
            ]);
        }

        return $this->messageRepository->findFirst($messageId);
    }
}
