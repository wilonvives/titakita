@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var \TitaKita\Services\Application\Handlers\Message\DTO\SendMessageDTO $messageData */ @endphp

@php /** @see \TitaKita\Mail\Event\EventMessage */ @endphp

<x-mail::message>
{!! $messageData->message !!}

{!! $eventSettings->getGetEmailFooterHtml() !!}

<div style="color: #888; margin-top: 30px; font-size: .8em;">
{{ __('You are receiving this communication because you are registered as an attendee for the following event:') }}
<b>{{ $event->getTitle() }}</b>. {{ __('If you believe you have received this email in error,') }}
{{ __('please contact the event organizer at') }} <a
        href="mailto:{{$eventSettings->getSupportEmail()}}">{{$eventSettings->getSupportEmail()}}</a>.
{{ __('If you believe this is spam, please report it to') }} <a href="mailto:{{config('mail.from.address')}}">{{config('mail.from.address')}}</a>.
</div>
</x-mail::message>
