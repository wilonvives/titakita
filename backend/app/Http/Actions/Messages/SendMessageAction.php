<?php

namespace TitaKita\Http\Actions\Messages;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\MessagingTierLimitExceededException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Message\SendMessageRequest;
use TitaKita\Resources\Message\MessageResource;
use TitaKita\Services\Application\Handlers\Message\DTO\SendMessageDTO;
use TitaKita\Services\Application\Handlers\Message\SendMessageHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SendMessageAction extends BaseAction
{
    private SendMessageHandler $messageHandler;

    public function __construct(SendMessageHandler $messageHandler)
    {
        $this->messageHandler = $messageHandler;
    }

    public function __invoke(SendMessageRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $user = $this->getAuthenticatedUser();

        try {
            $message = $this->messageHandler->handle(SendMessageDTO::fromArray([
                'event_id' => $eventId,
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'type' => $request->input('message_type'),
                'is_test' => $request->input('is_test'),
                'order_id' => $request->input('order_id'),
                'attendee_ids' => $request->input('attendee_ids'),
                'product_ids' => $request->input('product_ids'),
                'order_statuses' => $request->input('order_statuses'),
                'send_copy_to_current_user' => $request->boolean('send_copy_to_current_user'),
                'sent_by_user_id' => $user->getId(),
                'account_id' => $this->getAuthenticatedAccountId(),
                'scheduled_at' => $request->input('scheduled_at'),
            ]));
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (MessagingTierLimitExceededException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $this->resourceResponse(MessageResource::class, $message);
    }
}
