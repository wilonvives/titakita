<?php

namespace TitaKita\Mail\User;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Helper\Url;
use TitaKita\Mail\BaseMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/user/confirm-email-change.blade.php
 */
class ConfirmEmailChangeMail extends BaseMail
{
    private UserDomainObject $userDomainObject;

    private string $token;

    public function __construct(UserDomainObject $user, string $token)
    {
        parent::__construct();

        $this->userDomainObject = $user;
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Confirm email change'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user.confirm-email-change',
            with: [
                'user' => $this->userDomainObject,
                'link' => sprintf(Url::getFrontEndUrlFromConfig(Url::CONFIRM_EMAIL_CHANGE), $this->token),
            ]
        );
    }
}
