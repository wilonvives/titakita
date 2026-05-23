<?php

namespace TitaKita\Jobs\Waitlist;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Mail\Waitlist\WaitlistOfferExpiredMail;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWaitlistOfferExpiredEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly WaitlistEntryDomainObject $entry,
    )
    {
    }

    public function handle(
        EventRepositoryInterface      $eventRepository,
        ProductPriceRepositoryInterface $productPriceRepository,
        ProductRepositoryInterface    $productRepository,
        Mailer                        $mailer,
    ): void
    {
        $event = $eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->findById($this->entry->getEventId());

        $product = null;
        $productPrice = null;
        if ($this->entry->getProductPriceId()) {
            $productPrice = $productPriceRepository->findById($this->entry->getProductPriceId());
            $product = $productRepository->findById($productPrice->getProductId());
        }

        $mailer
            ->to($this->entry->getEmail())
            ->locale($this->entry->getLocale())
            ->send(new WaitlistOfferExpiredMail(
                entry: $this->entry,
                event: $event,
                product: $product,
                productPrice: $productPrice,
                organizer: $event->getOrganizer(),
                eventSettings: $event->getEventSettings(),
            ));
    }
}
