<?php

namespace TitaKita\Http\Request\Waitlist;

use TitaKita\Http\Request\BaseRequest;

class OfferWaitlistEntryRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'product_price_id' => ['required_without:entry_id', 'integer', 'exists:product_prices,id'],
            'entry_id' => ['required_without:product_price_id', 'integer', 'exists:waitlist_entries,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
