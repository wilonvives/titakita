<?php

namespace TitaKita\Http\Actions\Common;

use TitaKita\DomainObjects\Enums\ColorTheme;
use TitaKita\Http\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetColorThemesAction extends BaseAction
{
    public function __invoke(): JsonResponse
    {
        return $this->jsonResponse(
            data: ColorTheme::getAllThemes(),
            wrapInData: true,
        );
    }
}
