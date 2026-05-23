<?php

namespace TitaKita\Http\Actions\Auth;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Auth\AuthenticatedResponseResource;
use TitaKita\Services\Application\Handlers\Auth\DTO\AuthenticatedResponseDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

abstract class BaseAuthAction extends BaseAction
{
    protected function getAuthCookie(string $token): SymfonyCookie
    {
        // Secure/SameSite=None requires HTTPS; fall back to a plain cookie
        // when served over HTTP (e.g. IP/port self-host) so login persists.
        $secure = request()->isSecure();

        return Cookie::make(
            name: 'token',
            value: $token,
            secure: $secure,
            sameSite: $secure ? 'None' : 'Lax',
        );
    }

    protected function addTokenToResponse(JsonResponse|Response $response, ?string $token): JsonResponse
    {
        if (!$token) {
            return $response;
        }

        $response = $response->withCookie($this->getAuthCookie($token));

        $response->header('X-Auth-Token', $token);

        return $response;
    }

    protected function respondWithToken(?string $token, Collection $accounts): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        return $this->addTokenToResponse(
            response: $this->jsonResponse(new AuthenticatedResponseResource(new AuthenticatedResponseDTO(
                token: $token,
                expiresIn: auth()->factory()->getTTL() * 60,
                accounts: $accounts,
                user: $user,
            ))),
            token: $token
        );
    }
}
