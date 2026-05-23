<?php

namespace TitaKita\Jobs\Message;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\MessageDomainObject;
use TitaKita\Mail\Admin\MessagePendingReviewMail;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MessagePendingReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly int   $messageId,
        private readonly array $failures
    )
    {
    }

    public function handle(
        MessageRepositoryInterface $messageRepository,
        EventRepositoryInterface   $eventRepository,
        AccountRepositoryInterface $accountRepository,
        Mailer                     $mailer,
        Repository                 $config
    ): void
    {
        /** @var MessageDomainObject $message */
        $message = $messageRepository->findById($this->messageId);

        /** @var EventDomainObject $event */
        $event = $eventRepository->findById($message->getEventId());

        $account = $accountRepository->findByEventId($event->getId());

        $supportEmail = $config->get('app.platform_support_email');

        if ($supportEmail) {
            $mailer->to($supportEmail)->send(
                new MessagePendingReviewMail($message, $event, $account, $this->failures)
            );
        }
    }
}
