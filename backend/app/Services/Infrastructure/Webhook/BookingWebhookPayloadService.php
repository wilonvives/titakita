<?php

declare(strict_types=1);

namespace TitaKita\Services\Infrastructure\Webhook;

use Illuminate\Support\Collection;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\Enums\QuestionTypeEnum;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;

/**
 * Builds the enriched payload emitted for booking.* webhooks: workshop/event
 * info, the booked session's start/end + location, the order, and each
 * attendee's name/email/phone. Used only for events with event_type = booking.
 */
class BookingWebhookPayloadService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(OrderDomainObject $order): array
    {
        $event = $order->getEvent() ?? $this->eventRepository->findById($order->getEventId());

        $sessionProducts = $this->resolveSessionProducts($order);

        return [
            'event' => $this->buildEventPayload($event),
            'session' => $this->buildSessionPayload($sessionProducts->first()),
            'sessions' => $sessionProducts
                ->map(fn (ProductDomainObject $product) => $this->buildSessionPayload($product))
                ->values()
                ->all(),
            'order' => [
                'id' => $order->getId(),
                'short_id' => $order->getShortId(),
                'status' => $order->getStatus(),
                'first_name' => $order->getFirstName(),
                'last_name' => $order->getLastName(),
                'email' => $order->getEmail(),
            ],
            'attendees' => $this->buildAttendeesPayload($order->getAttendees()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEventPayload(EventDomainObject $event): array
    {
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'event_type' => $event->getEventType(),
            'timezone' => $event->getTimezone(),
            'location' => $event->getLocationDetails(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSessionPayload(?ProductDomainObject $product): ?array
    {
        if ($product === null) {
            return null;
        }

        return [
            'product_id' => $product->getId(),
            'title' => $product->getTitle(),
            'session_start_at' => $product->getSessionStartAt(),
            'session_end_at' => $product->getSessionEndAt(),
        ];
    }

    /**
     * @return Collection<int, ProductDomainObject>
     */
    private function resolveSessionProducts(OrderDomainObject $order): Collection
    {
        $orderItems = $order->getOrderItems() ?? new Collection;

        $productIds = $orderItems
            ->map(fn (OrderItemDomainObject $item) => $item->getProductId())
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return new Collection;
        }

        return $this->productRepository
            ->findWhereIn('id', $productIds->all())
            ->filter(fn (ProductDomainObject $product) => $product->getScheduleId() !== null)
            ->values();
    }

    /**
     * @param  Collection<int, AttendeeDomainObject>|null  $attendees
     * @return array<int, array<string, mixed>>
     */
    private function buildAttendeesPayload(?Collection $attendees): array
    {
        if ($attendees === null) {
            return [];
        }

        return $attendees
            ->map(fn (AttendeeDomainObject $attendee) => [
                'id' => $attendee->getId(),
                'first_name' => $attendee->getFirstName(),
                'last_name' => $attendee->getLastName(),
                'email' => $attendee->getEmail(),
                'phone' => $this->resolvePhone($attendee),
            ])
            ->values()
            ->all();
    }

    private function resolvePhone(AttendeeDomainObject $attendee): ?string
    {
        $answers = $attendee->getQuestionAndAnswerViews();

        if (! $answers instanceof Collection) {
            return null;
        }

        /** @var QuestionAndAnswerViewDomainObject|null $phoneAnswer */
        $phoneAnswer = $answers->first(
            fn (QuestionAndAnswerViewDomainObject $answer) => $answer->getQuestionType() === QuestionTypeEnum::PHONE->name
        );

        if ($phoneAnswer === null) {
            return null;
        }

        $answer = $phoneAnswer->getAnswer();

        return is_array($answer) ? (string) ($answer[0] ?? '') : $answer;
    }
}
