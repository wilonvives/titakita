@php /** @var \TitaKita\DomainObjects\WaitlistEntryDomainObject $entry */ @endphp
@php /** @var \TitaKita\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var ?string $productName */ @endphp
@php /** @var \TitaKita\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \TitaKita\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $eventUrl */ @endphp

@php /** @see \TitaKita\Mail\Waitlist\WaitlistConfirmationMail */ @endphp

<x-mail::message>
# {{ __("You're on the waitlist!") }}

{{ __('Hello') }},

@if($productName)
{{ __("You have been added to the waitlist for **:product** for the event **:event**.", ['product' => $productName, 'event' => $event->getTitle()]) }}
@else
{{ __("You have been added to the waitlist for the event **:event**.", ['event' => $event->getTitle()]) }}
@endif

{{ __("We'll notify you as soon as a spot becomes available.") }}

<x-mail::button :url="$eventUrl">
{{ __('View Event') }}
</x-mail::button>

{{ __('If you have any questions or need assistance, please respond to this email.') }}

{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
