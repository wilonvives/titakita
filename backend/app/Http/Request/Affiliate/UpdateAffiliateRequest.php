<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\Affiliate;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\AffiliateRules;

class UpdateAffiliateRequest extends BaseRequest
{
    public function rules(): array
    {
        return AffiliateRules::updateRules();
    }
}