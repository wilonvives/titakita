@php /** @var \TitaKita\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \TitaKita\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $ticketUrl */ @endphp

@php /** @see \TitaKita\Mail\Order\OrderCancelled */ @endphp

<x-mail::message>
{{ __('Hello') }},

{{ __('Your order for') }} <b>{{$event->getTitle()}}</b> {{ __('has been cancelled.') }}
<br>
<br>
{{ __('Order #:') }} <b>{{$order->getPublicId()}}</b>
<br>
<br>
{{ __('If you have any questions or need assistance, please respond to this email.') }}
<br><br>
{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
