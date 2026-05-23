<?php

namespace TitaKita\Http\Actions\Messages;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\MessageDomainObject;
use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use TitaKita\Resources\Message\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMessagesAction extends BaseAction
{
    private MessageRepositoryInterface $messageRepository;

    public function __construct(MessageRepositoryInterface $MessageRepository)
    {
        $this->messageRepository = $MessageRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $messages = $this->messageRepository
            ->loadRelation(new Relationship(UserDomainObject::class, name: 'sent_by_user'))
            ->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: MessageResource::class,
            data: $messages,
            domainObject: MessageDomainObject::class
        );
    }
}
