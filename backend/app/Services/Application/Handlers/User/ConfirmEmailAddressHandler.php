<?php

namespace TitaKita\Services\Application\Handlers\User;

use TitaKita\Services\Application\Handlers\User\DTO\ConfirmEmailChangeDTO;
use TitaKita\Services\Domain\User\EmailConfirmationService;
use TitaKita\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Throwable;

readonly class ConfirmEmailAddressHandler
{
    public function __construct(
        private EmailConfirmationService $emailConfirmationService,
    )
    {
    }

    /**
     * @throws DecryptionFailedException|Throwable
     */
    public function handle(ConfirmEmailChangeDTO $data): void
    {
        $this->emailConfirmationService->confirmEmailAddress($data->token, $data->accountId);
    }
}
