<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Admin;

use Carbon\Carbon;
use TitaKita\DomainObjects\MessageDomainObject;
use TitaKita\DomainObjects\Status\MessageStatus;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use TitaKita\Services\Domain\Message\MessageDispatchService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class ApproveMessageHandler
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly DatabaseManager            $databaseManager,
        private readonly MessageDispatchService     $messageDispatchService,
    )
    {
    }

    public function handle(int $messageId): MessageDomainObject
    {
        return $this->databaseManager->transaction(function () use ($messageId) {
            return $this->approveMessage($messageId);
        });
    }

    private function approveMessage(int $messageId): MessageDomainObject
    {
        $message = $this->messageRepository->findFirst($messageId);

        if ($message === null) {
            throw new ResourceNotFoundException(__('Message not found'));
        }

        if ($message->getStatus() !== MessageStatus::PENDING_REVIEW->name) {
            throw ValidationException::withMessages([
                'status' => [__('Message must be in pending review status to be approved')],
            ]);
        }

        $scheduledAt = $message->getScheduledAt();
        $isFutureScheduled = $scheduledAt !== null && Carbon::parse($scheduledAt)->isFuture();

        if ($isFutureScheduled) {
            return $this->messageRepository->updateFromArray($messageId, [
                'status' => MessageStatus::SCHEDULED->name,
            ]);
        }

        $this->messageDispatchService->dispatchMessage($message, MessageStatus::PENDING_REVIEW);

        return $this->messageRepository->findFirst($messageId);
    }
}
