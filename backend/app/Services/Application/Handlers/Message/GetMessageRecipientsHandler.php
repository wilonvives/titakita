<?php

namespace TitaKita\Services\Application\Handlers\Message;

use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use TitaKita\Repository\Interfaces\OutgoingMessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetMessageRecipientsHandler
{
    public function __construct(
        private readonly OutgoingMessageRepositoryInterface $outgoingMessageRepository,
        private readonly MessageRepositoryInterface         $messageRepository,
    )
    {
    }

    public function handle(int $eventId, int $messageId, QueryParamsDTO $params): LengthAwarePaginator
    {
        $message = $this->messageRepository->findFirstWhere([
            'id' => $messageId,
            'event_id' => $eventId,
        ]);

        if ($message === null) {
            throw new ResourceNotFoundException(__('Message not found'));
        }

        return $this->outgoingMessageRepository->paginateWhere(
            where: [
                'event_id' => $eventId,
                'message_id' => $messageId,
            ],
            limit: $params->per_page,
        );
    }
}
