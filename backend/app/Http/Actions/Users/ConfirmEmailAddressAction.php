<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\User\ConfirmEmailAddressHandler;
use TitaKita\Services\Application\Handlers\User\DTO\ConfirmEmailChangeDTO;
use TitaKita\Services\Infrastructure\Encryption\Exception\DecryptionFailedException;
use TitaKita\Services\Infrastructure\Encryption\Exception\EncryptedPayloadExpiredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class ConfirmEmailAddressAction extends BaseAction
{
    public function __construct(
        private readonly ConfirmEmailAddressHandler $confirmEmailAddressHandler
    )
    {
    }

    /**
     * @throws DecryptionFailedException|Throwable
     */
    public function __invoke(int $userId, string $resetToken): Response|JsonResponse
    {
        $this->isActionAuthorized($userId, UserDomainObject::class);

        try {
            $this->confirmEmailAddressHandler->handle(new ConfirmEmailChangeDTO(
                token: $resetToken,
                accountId: $this->getAuthenticatedAccountId(),
            ));
        } catch (EncryptedPayloadExpiredException) {
            return $this->errorResponse(__('The email confirmation link has expired. Please request a new one.'));
        } catch (DecryptionFailedException) {
            return $this->errorResponse(__('The email confirmation link is invalid.'));
        }

        return $this->noContentResponse();
    }
}
