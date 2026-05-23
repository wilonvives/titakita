@php /** @var \TitaKita\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \TitaKita\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $eventUrl */ @endphp

@php /** @see \TitaKita\Mail\Order\OrderFailed */ @endphp

<x-mail::message>
{{ __('Hello') }},

{{ __('Your recent order for') }} <b>{{$event->getTitle()}}</b> {{ __('was not successful.') }}

<x-mail::button :url="$eventUrl">
{{ __('View Event Homepage') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, feel free to reach out to our support team') }}
{{ __('at') }} {{ $supportEmail ?? 'hello@hi.events' }}.

{{ __('Best regards') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
