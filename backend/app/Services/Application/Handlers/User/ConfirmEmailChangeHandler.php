<?php

namespace TitaKita\Services\Application\Handlers\User;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Services\Application\Handlers\User\DTO\ConfirmEmailChangeDTO;
use TitaKita\Services\Infrastructure\Encryption\EncryptedPayloadService;
use TitaKita\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class ConfirmEmailChangeHandler
{
    public function __construct(
        private LoggerInterface         $logger,
        private UserRepositoryInterface $userRepository,
        private EncryptedPayloadService $encryptedPayloadService,
        private DatabaseManager         $databaseManager,
    )
    {
    }

    /**
     * @throws DecryptionFailedException|Throwable
     */
    public function handle(ConfirmEmailChangeDTO $data): UserDomainObject
    {
        return $this->databaseManager->transaction(function () use ($data) {
            ['id' => $userId] = $this->encryptedPayloadService->decryptPayload($data->token);

            $user = $this->userRepository->findByIdAndAccountId($userId, $data->accountId);

            if ($user->getPendingEmail() === null) {
                throw new ResourceConflictException(__('No email change pending'));
            }

            $this->userRepository->updateWhere(
                attributes: [
                    'email' => $user->getPendingEmail(),
                    'pending_email' => null,
                ],
                where: [
                    'id' => $userId,
                ]
            );

            $this->logger->info('Confirming email change', [
                'user_id' => $userId,
                'old_email' => $user->getEmail(),
                'new_email' => $user->getPendingEmail(),
            ]);

            return $this->userRepository->findByIdAndAccountId($userId, $data->accountId);
        });
    }
}
