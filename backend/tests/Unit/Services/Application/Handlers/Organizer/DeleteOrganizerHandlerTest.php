<?php

namespace Tests\Unit\Services\Application\Handlers\Organizer;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Repository\Interfaces\OrganizerRepositoryInterface;
use TitaKita\Services\Application\Handlers\Organizer\DeleteOrganizerHandler;
use TitaKita\Services\Application\Handlers\Organizer\DTO\DeleteOrganizerDTO;
use TitaKita\Services\Domain\Event\EventDeletionService;
use TitaKita\Services\Domain\Organizer\OrganizerDeletionService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use Psr\Log\LoggerInterface;

class DeleteOrganizerHandlerTest extends TestCase
{
    private OrganizerRepositoryInterface $organizerRepository;
    private EventRepositoryInterface $eventRepository;
    private OrderRepositoryInterface $orderRepository;
    private DeleteOrganizerHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizerRepository = m::mock(OrganizerRepositoryInterface::class);
        $this->eventRepository = m::mock(EventRepositoryInterface::class);
        $this->orderRepository = m::mock(OrderRepositoryInterface::class);
        $logger = m::mock(LoggerInterface::class);
        $databaseManager = m::mock(DatabaseManager::class);
        $eventLogger = m::mock(LoggerInterface::class);
        $eventDatabaseManager = m::mock(DatabaseManager::class);

        $databaseManager->shouldReceive('transaction')
            ->andReturnUsing(fn($callback) => $callback());

        $logger->shouldReceive('info')->byDefault();
        $eventLogger->shouldReceive('info')->byDefault();

        $eventDeletionService = new EventDeletionService(
            $this->eventRepository,
            $this->orderRepository,
            $eventLogger,
            $eventDatabaseManager,
        );

        $organizerDeletionService = new OrganizerDeletionService(
            $this->organizerRepository,
            $this->eventRepository,
            $eventDeletionService,
            $logger,
            $databaseManager,
        );

        $this->handler = new DeleteOrganizerHandler($organizerDeletionService);
    }

    public function testDeleteOrganizerSuccessfully(): void
    {
        $this->organizerRepository->shouldReceive('countWhere')
            ->with(['account_id' => 10])
            ->andReturn(2);

        $this->eventRepository->shouldReceive('findWhere')
            ->with(['organizer_id' => 1])
            ->andReturn(new Collection());

        $this->organizerRepository->shouldReceive('deleteWhere')
            ->once()
            ->with(['id' => 1, 'account_id' => 10])
            ->andReturn(1);

        $dto = new DeleteOrganizerDTO(organizerId: 1, accountId: 10);

        $this->handler->handle($dto);

        $this->assertTrue(true);
    }

    public function testDeleteOrganizerFailsWithCompletedOrders(): void
    {
        $event = m::mock(EventDomainObject::class);
        $event->shouldReceive('getId')->andReturn(100);

        $this->organizerRepository->shouldReceive('countWhere')
            ->with(['account_id' => 10])
            ->andReturn(2);

        $this->eventRepository->shouldReceive('findWhere')
            ->with(['organizer_id' => 1])
            ->andReturn(new Collection([$event]));

        $paidOrder = m::mock(OrderDomainObject::class);
        $paidOrder->shouldReceive('getTotalGross')->andReturn(50.0);
        $paidOrder->shouldReceive('getTotalRefunded')->andReturn(0.0);

        $this->orderRepository->shouldReceive('findWhere')
            ->with(['event_id' => 100, 'status' => 'COMPLETED'])
            ->andReturn(new Collection([$paidOrder]));

        $dto = new DeleteOrganizerDTO(organizerId: 1, accountId: 10);

        $this->expectException(CannotDeleteEntityException::class);

        $this->handler->handle($dto);
    }

    public function testDeleteLastOrganizerFails(): void
    {
        $this->organizerRepository->shouldReceive('countWhere')
            ->with(['account_id' => 10])
            ->andReturn(1);

        $this->eventRepository->shouldReceive('findWhere')
            ->with(['organizer_id' => 1])
            ->andReturn(new Collection());

        $dto = new DeleteOrganizerDTO(organizerId: 1, accountId: 10);

        $this->expectException(CannotDeleteEntityException::class);

        $this->handler->handle($dto);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
