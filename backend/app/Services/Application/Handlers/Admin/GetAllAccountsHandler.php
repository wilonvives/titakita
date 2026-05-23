<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetAllAccountsDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetAllAccountsHandler
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository,
    )
    {
    }

    public function handle(GetAllAccountsDTO $dto): LengthAwarePaginator
    {
        return $this->accountRepository->getAllAccountsWithCounts(
            search: $dto->search,
            perPage: $dto->perPage,
        );
    }
}
