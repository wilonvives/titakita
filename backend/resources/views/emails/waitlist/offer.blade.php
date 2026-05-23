@php /** @var \TitaKita\DomainObjects\WaitlistEntryDomainObject $entry */ @endphp
@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var ?string $productName */ @endphp
@php /** @var \TitaKita\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $checkoutUrl */ @endphp

@php /** @see \TitaKita\Mail\Waitlist\WaitlistOfferMail */ @endphp

<x-mail::message>
# {{ __('A spot has opened up!') }}

{{ __('Hello') }},

@if($productName)
{{ __('Great news! A spot has become available for **:product** for the event **:event**.', ['product' => $productName, 'event' => $event->getTitle()]) }}
@else
{{ __('Great news! A spot has become available for the event **:event**.', ['event' => $event->getTitle()]) }}
@endif

{{ __('An order has been reserved for you. Click the button below to complete your purchase.') }}

@if($offerExpiresAtFormatted)
{{ __('This offer expires on :date. Please complete your order before it expires.', ['date' => $offerExpiresAtFormatted]) }}
@endif

<x-mail::button :url="$checkoutUrl">
{{ __('Complete Your Order') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, please respond to this email.') }}

{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
