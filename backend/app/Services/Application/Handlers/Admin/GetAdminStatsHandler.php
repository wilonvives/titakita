<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\DomainObjects\Status\EventStatus;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetAdminStatsDTO;

class GetAdminStatsHandler
{
    public function __construct(
        private readonly UserRepositoryInterface     $userRepository,
        private readonly AccountRepositoryInterface  $accountRepository,
        private readonly EventRepositoryInterface    $eventRepository,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
    )
    {
    }

    public function handle(): GetAdminStatsDTO
    {
        $totalUsers = $this->userRepository->countWhere([]);
        $totalAccounts = $this->accountRepository->countWhere([]);
        $totalLiveEvents = $this->eventRepository->countWhere(['status' => EventStatus::LIVE->name]);
        $totalTicketsSold = $this->attendeeRepository->countWhere(['status' => AttendeeStatus::ACTIVE->name]);

        return new GetAdminStatsDTO(
            total_users: $totalUsers,
            total_accounts: $totalAccounts,
            total_live_events: $totalLiveEvents,
            total_tickets_sold: $totalTicketsSold,
        );
    }
}
