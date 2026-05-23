<?php

namespace TitaKita\Http\Actions\EmailTemplates;

use TitaKita\DomainObjects\Enums\EmailTemplateType;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\ResponseCodes;
use TitaKita\Services\Application\Handlers\EmailTemplate\GetAvailableTokensHandler;
use Illuminate\Http\JsonResponse;

class GetAvailableTokensAction extends BaseAction
{
    public function __construct(
        private readonly GetAvailableTokensHandler $handler
    ) {
    }

    public function __invoke(string $templateType): JsonResponse
    {
        //no authorization needed

        $type = EmailTemplateType::tryFrom($templateType);

        if (!$type) {
            return $this->jsonResponse(['error' => __('Invalid template type')], ResponseCodes::HTTP_BAD_REQUEST);
        }

        $tokens = $this->handler->handle($type);

        return $this->jsonResponse(['tokens' => $tokens]);
    }
}
