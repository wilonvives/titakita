<?php

namespace TitaKita\Services\Infrastructure\Webhook;

use Illuminate\Http\Resources\Json\JsonResource;
use Psr\Log\LoggerInterface;
use Spatie\WebhookServer\WebhookCall;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\Enums\EventType;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\DomainObjects\WebhookDomainObject;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeCheckInRepositoryInterface;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Repository\Interfaces\WebhookRepositoryInterface;
use TitaKita\Resources\Attendee\AttendeeResource;
use TitaKita\Resources\CheckInList\AttendeeCheckInResource;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Resources\Order\OrderResource;
use TitaKita\Resources\Product\ProductResource;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;

class WebhookDispatchService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly WebhookRepositoryInterface $webhookRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly AttendeeCheckInRepositoryInterface $attendeeCheckInRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly BookingWebhookPayloadService $bookingWebhookPayloadService,
    ) {}

    public function dispatchEventWebhook(DomainEventType $eventType, int $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new EventResource($event),
            eventId: $eventId,
        );
    }

    public function dispatchAttendeeWebhook(DomainEventType $eventType, int $attendeeId): void
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: QuestionAndAnswerViewDomainObject::class,
                name: 'question_and_answer_views',
            ))
            ->findById($attendeeId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new AttendeeResource($attendee),
            eventId: $attendee->getEventId(),
        );
    }

    public function dispatchCheckInWebhook(DomainEventType $eventType, int $attendeeCheckInId): void
    {
        $attendeeCheckIn = $this->attendeeCheckInRepository
            ->loadRelation(new Relationship(
                domainObject: AttendeeDomainObject::class,
                name: 'attendee',
            ))
            ->includeDeleted()
            ->findById($attendeeCheckInId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new AttendeeCheckInResource($attendeeCheckIn),
            eventId: $attendeeCheckIn->getEventId(),
        );
    }

    public function dispatchProductWebhook(DomainEventType $eventType, int $productId): void
    {
        $product = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->loadRelation(TaxAndFeesDomainObject::class)
            ->includeDeleted()
            ->findById($productId);

        $this->dispatchWebhook(
            eventType: $eventType,
            payload: new ProductResource($product),
            eventId: $product->getEventId(),
        );
    }

    public function dispatchOrderWebhook(DomainEventType $eventType, int $orderId): void
    {
        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(new Relationship(
                domainObject: AttendeeDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: QuestionAndAnswerViewDomainObject::class,
                        name: 'question_and_answer_views',
                    ),
                ],
                name: 'attendees')
            )
            ->loadRelation(QuestionAndAnswerViewDomainObject::class)
            ->findById($orderId);

        if ($eventType === DomainEventType::ORDER_CREATED) {
            /** @var AttendeeDomainObject $attendee */
            foreach ($order->getAttendees() as $attendee) {
                $this->dispatchAttendeeWebhook(
                    eventType: DomainEventType::ATTENDEE_CREATED,
                    attendeeId: $attendee->getId(),
                );
            }
        }

        if ($eventType === DomainEventType::ORDER_CANCELLED) {
            /** @var AttendeeDomainObject $attendee */
            foreach ($order->getAttendees() as $attendee) {
                $this->dispatchAttendeeWebhook(
                    eventType: DomainEventType::ATTENDEE_CANCELLED,
                    attendeeId: $attendee->getId(),
                );
            }
        }

        $this->dispatchWebhook(
            $eventType,
            new OrderResource($order),
            $order->getEventId(),
        );

        $this->dispatchBookingWebhookIfApplicable($eventType, $order);
    }

    /**
     * Additive: for events with event_type = booking, also emit an enriched
     * booking.created / booking.cancelled webhook carrying the workshop info,
     * the booked session(s), the order and each attendee (name/email/phone).
     * Non-booking events are completely unaffected.
     */
    private function dispatchBookingWebhookIfApplicable(DomainEventType $eventType, OrderDomainObject $order): void
    {
        $bookingEventType = match ($eventType) {
            DomainEventType::ORDER_CREATED => DomainEventType::BOOKING_CREATED,
            DomainEventType::ORDER_CANCELLED => DomainEventType::BOOKING_CANCELLED,
            default => null,
        };

        if ($bookingEventType === null) {
            return;
        }

        $event = $this->eventRepository->findById($order->getEventId());

        if ($event->getEventType() !== EventType::BOOKING->value) {
            return;
        }

        $order->setEvent($event);

        $this->dispatchWebhookWithPayload(
            eventType: $bookingEventType,
            payload: $this->bookingWebhookPayloadService->build($order),
            eventId: $order->getEventId(),
        );
    }

    private function dispatchWebhook(DomainEventType $eventType, JsonResource $payload, int $eventId): void
    {
        $this->dispatchWebhookWithPayload($eventType, $payload->resolve(), $eventId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchWebhookWithPayload(DomainEventType $eventType, array $payload, int $eventId): void
    {
        $webhooks = $this->webhookRepository->findEnabledByEventId($eventId)
            ->filter(fn (WebhookDomainObject $webhook) => in_array($eventType->value, $webhook->getEventTypes(), true));

        foreach ($webhooks as $webhook) {
            $this->logger->info("Dispatching webhook for event ID: $eventId and webhook ID: {$webhook->getId()}");

            WebhookCall::create()
                ->url($webhook->getUrl())
                ->payload([
                    'event_type' => $eventType->value,
                    'event_sent_at' => now()->toIso8601String(),
                    'payload' => $payload,
                ])
                ->useSecret($webhook->getSecret())
                ->meta([
                    'webhook_id' => $webhook->getId(),
                    'event_id' => $eventId,
                    'event_type' => $eventType->name,
                ])
                ->dispatchSync();
        }
    }
}
