<?php

namespace Tests\Unit\Services\Application\Handlers\Event;

use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\Event\DeleteEventHandler;
use TitaKita\Services\Application\Handlers\Event\DTO\DeleteEventDTO;
use TitaKita\Services\Domain\Event\EventDeletionService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use Psr\Log\LoggerInterface;

class DeleteEventHandlerTest extends TestCase
{
    private EventDeletionService $eventDeletionService;
    private DeleteEventHandler $handler;
    private OrderRepositoryInterface $orderRepository;
    private EventRepositoryInterface $eventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = m::mock(EventRepositoryInterface::class);
        $this->orderRepository = m::mock(OrderRepositoryInterface::class);
        $logger = m::mock(LoggerInterface::class);
        $databaseManager = m::mock(DatabaseManager::class);

        $databaseManager->shouldReceive('transaction')
            ->andReturnUsing(fn ($callback) => $callback());

        $this->eventDeletionService = new EventDeletionService(
            $this->eventRepository,
            $this->orderRepository,
            $logger,
            $databaseManager,
        );

        $this->handler = new DeleteEventHandler($this->eventDeletionService);

        $this->eventRepository->shouldReceive('deleteWhere')->byDefault()->andReturn(1);
        $logger->shouldReceive('info')->byDefault();
    }

    private function order(float $gross, float $refunded): OrderDomainObject
    {
        $order = m::mock(OrderDomainObject::class);
        $order->shouldReceive('getTotalGross')->andReturn($gross);
        $order->shouldReceive('getTotalRefunded')->andReturn($refunded);

        return $order;
    }

    private function expectCompletedOrders(Collection $orders): void
    {
        $this->orderRepository->shouldReceive('findWhere')
            ->with(['event_id' => 1, 'status' => 'COMPLETED'])
            ->andReturn($orders);
    }

    public function testDeleteEventSucceedsWithNoOrders(): void
    {
        $this->expectCompletedOrders(collect([]));
        $this->eventRepository->shouldReceive('deleteWhere')
            ->once()->with(['id' => 1, 'account_id' => 10])->andReturn(1);

        $this->handler->handle(new DeleteEventDTO(eventId: 1, accountId: 10));

        $this->assertTrue(true);
    }

    public function testDeleteEventSucceedsWithFreeRegistrations(): void
    {
        $this->expectCompletedOrders(collect([$this->order(0.0, 0.0), $this->order(0.0, 0.0)]));
        $this->eventRepository->shouldReceive('deleteWhere')->once()->andReturn(1);

        $this->handler->handle(new DeleteEventDTO(eventId: 1, accountId: 10));

        $this->assertTrue(true);
    }

    public function testDeleteEventSucceedsWhenPaidOrdersFullyRefunded(): void
    {
        $this->expectCompletedOrders(collect([$this->order(50.0, 50.0)]));
        $this->eventRepository->shouldReceive('deleteWhere')->once()->andReturn(1);

        $this->handler->handle(new DeleteEventDTO(eventId: 1, accountId: 10));

        $this->assertTrue(true);
    }

    public function testDeleteEventFailsWhenPaidOrderNotRefunded(): void
    {
        $this->expectCompletedOrders(collect([$this->order(0.0, 0.0), $this->order(50.0, 0.0)]));

        $this->expectException(CannotDeleteEntityException::class);

        $this->handler->handle(new DeleteEventDTO(eventId: 1, accountId: 10));
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
