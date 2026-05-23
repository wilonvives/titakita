<?php

namespace TitaKita\Http\Request\Webhook;

use TitaKita\DomainObjects\Status\WebhookStatus;
use TitaKita\Http\Request\BaseRequest;
use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;
use TitaKita\Validators\Rules\NoInternalUrlRule;
use Illuminate\Validation\Rule;

class UpsertWebhookRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', new NoInternalUrlRule()],
            'event_types.*' => ['required', Rule::in(DomainEventType::valuesArray())],
            'status' => ['nullable', Rule::in(WebhookStatus::valuesArray())],
        ];
    }
}
