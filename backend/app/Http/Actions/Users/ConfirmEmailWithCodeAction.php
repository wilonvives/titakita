<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\User\ConfirmEmailWithCodeHandler;
use TitaKita\Services\Application\Handlers\User\DTO\ConfirmEmailWithCodeDTO;
use TitaKita\Services\Application\Handlers\User\Exception\InvalidEmailVerificationCodeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfirmEmailWithCodeAction extends BaseAction
{
    public function __construct(
        private readonly ConfirmEmailWithCodeHandler $confirmEmailWithCodeHandler
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request): ValidationException|JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        try {
            $this->confirmEmailWithCodeHandler->handle(
                ConfirmEmailWithCodeDTO::from([
                    'code' => $request->input('code'),
                    'userId' => $user->getId(),
                    'accountId' => $this->getAuthenticatedAccountId(),
                ])
            );
        } catch (InvalidEmailVerificationCodeException $e) {
            throw ValidationException::withMessages([
                'code' => $e->getMessage(),
            ]);
        }

        return $this->jsonResponse([
            'message' => __('Your email has been successfully verified!'),
        ]);
    }
}
