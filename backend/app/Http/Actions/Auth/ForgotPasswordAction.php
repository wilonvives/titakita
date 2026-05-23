<?php

namespace TitaKita\Http\Actions\Auth;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Auth\ForgotPasswordRequest;
use TitaKita\Services\Application\Handlers\Auth\ForgotPasswordHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ForgotPasswordAction extends BaseAction
{
    private ForgotPasswordHandler $forgotPasswordHandler;

    public function __construct(ForgotPasswordHandler $forgotPasswordHandler)
    {
        $this->forgotPasswordHandler = $forgotPasswordHandler;
    }

    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->forgotPasswordHandler->handle($request->validated('email'));
        } catch (ResourceNotFoundException) {
            // swallow the exception to prevent leaking whether an email address is known
        }

        return $this->jsonResponse(
            data: [
                'message' => 'If the email address is known, an email has been sent with further instructions.',
            ]
        );
    }
}
