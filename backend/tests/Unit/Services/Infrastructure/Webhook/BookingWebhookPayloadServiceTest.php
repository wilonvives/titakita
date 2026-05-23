<?php

namespace Tests\Unit\Services\Infrastructure\Webhook;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\Enums\QuestionTypeEnum;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrderItemDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Application\Handlers\Booking\UpsertScheduleHandler;
use TitaKita\Services\Infrastructure\Webhook\BookingWebhookPayloadService;

class BookingWebhookPayloadServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function eventId(): int
    {
        $event = app(EventRepositoryInterface::class)->findFirstWhere([]);
        $this->assertNotNull($event, 'Expected at least one seeded event to exist.');

        return $event->getId();
    }

    private function generateSessionProduct(int $eventId): ProductDomainObject
    {
        app(UpsertScheduleHandler::class)->handle(UpsertScheduleDTO::from([
            'event_id' => $eventId,
            'session_duration_minutes' => 120,
            'start_times' => ['10:00'],
            'capacity_per_session' => 8,
            'scope_type' => ScheduleScopeType::SINGLE_DAY,
            'range_start_date' => '2026-06-06',
        ]));

        return app(ProductRepositoryInterface::class)
            ->findWhere([ProductDomainObjectAbstract::EVENT_ID => $eventId])
            ->first(fn (ProductDomainObject $p) => $p->getScheduleId() !== null);
    }

    public function test_payload_contains_event_session_order_and_attendees(): void
    {
        $eventId = $this->eventId();
        $sessionProduct = $this->generateSessionProduct($eventId);

        $attendee = (new AttendeeDomainObject)
            ->setId(1)
            ->setEventId($eventId)
            ->setProductId($sessionProduct->getId())
            ->setFirstName('Ada')
            ->setLastName('Lovelace')
            ->setEmail('ada@example.com')
            ->setQuestionAndAnswerViews(new Collection([
                (new QuestionAndAnswerViewDomainObject)
                    ->setQuestionType(QuestionTypeEnum::PHONE->name)
                    ->setAnswer('+1-555-0100')
                    ->setAttendeeId(1),
            ]));

        $order = (new OrderDomainObject)
            ->setId(42)
            ->setShortId('ORD-42')
            ->setEventId($eventId)
            ->setFirstName('Ada')
            ->setLastName('Lovelace')
            ->setEmail('ada@example.com')
            ->setStatus('COMPLETED')
            ->setOrderItems(new Collection([
                (new OrderItemDomainObject)
                    ->setId(1)
                    ->setProductId($sessionProduct->getId())
                    ->setQuantity(1),
            ]))
            ->setAttendees(new Collection([$attendee]));

        $payload = app(BookingWebhookPayloadService::class)->build($order);

        // Workshop / event info
        $this->assertArrayHasKey('event', $payload);
        $this->assertSame($eventId, $payload['event']['id']);
        $this->assertArrayHasKey('title', $payload['event']);
        $this->assertArrayHasKey('location', $payload['event']);

        // Session start/end
        $this->assertArrayHasKey('session', $payload);
        $this->assertSame($sessionProduct->getId(), $payload['session']['product_id']);
        $this->assertSame($sessionProduct->getSessionStartAt(), $payload['session']['session_start_at']);
        $this->assertSame($sessionProduct->getSessionEndAt(), $payload['session']['session_end_at']);

        // Order
        $this->assertArrayHasKey('order', $payload);
        $this->assertSame(42, $payload['order']['id']);
        $this->assertSame('ORD-42', $payload['order']['short_id']);

        // Attendees with name/email/phone
        $this->assertArrayHasKey('attendees', $payload);
        $this->assertCount(1, $payload['attendees']);
        $first = $payload['attendees'][0];
        $this->assertSame('Ada', $first['first_name']);
        $this->assertSame('Lovelace', $first['last_name']);
        $this->assertSame('ada@example.com', $first['email']);
        $this->assertSame('+1-555-0100', $first['phone']);
    }
}
