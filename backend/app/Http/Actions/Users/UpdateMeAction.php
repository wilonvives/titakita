<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\Exceptions\PasswordInvalidException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\User\UpdateMeRequest;
use TitaKita\Resources\User\UserResource;
use TitaKita\Services\Application\Handlers\User\DTO\UpdateMeDTO;
use TitaKita\Services\Application\Handlers\User\UpdateMeHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UpdateMeAction extends BaseAction
{
    private UpdateMeHandler $updateUserHandler;

    public function __construct(UpdateMeHandler $updateUserHandler)
    {
        $this->updateUserHandler = $updateUserHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(UpdateMeRequest $request): JsonResponse
    {
        try {
            $user = $this->updateUserHandler->handle(UpdateMeDTO::fromArray([
                'id' => $this->getAuthenticatedUser()->getId(),
                'account_id' => $this->getAuthenticatedAccountId(),
                'first_name' => $request->validated('first_name'),
                'last_name' => $request->validated('last_name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'current_password' => $request->validated('current_password'),
                'timezone' => $request->validated('timezone'),
                'locale' => $request->validated('locale'),
                'marketing_opt_in' => $request->has('marketing_opt_in') ? (bool) $request->validated('marketing_opt_in') : null,
            ]));

            return $this->resourceResponse(UserResource::class, $user);
        } catch (PasswordInvalidException) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password does not match our records.',
            ]);
        }
    }
}
