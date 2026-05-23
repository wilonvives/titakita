@php /** @var \TitaKita\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \TitaKita\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \TitaKita\Values\MoneyValue $refundAmount */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp

@php /** @see \TitaKita\Mail\Order\OrderRefunded */ @endphp

<x-mail::message>
{{ __('Hello') }},

{{ __('You have received a refund of :refundAmount for the following event: :eventTitle.', ['refundAmount' => $refundAmount, 'eventTitle' => $event->getTitle()]) }}

{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
