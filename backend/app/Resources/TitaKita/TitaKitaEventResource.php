<?php

namespace HiEvents\Resources\TitaKita;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Helper\Url;
use HiEvents\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * Maps a public (LIVE) event to a schema.org-aligned shape for the TitaKita
 * directory / AI discovery feed. Every item carries a canonical backlink URL so
 * the directory can drive users back to the source node.
 *
 * @mixin EventDomainObject
 */
class TitaKitaEventResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $publicUrl = rtrim((string)config('titakita.public_url'), '/');
        $eventUrl = $publicUrl . sprintf('/event/%d/%s', $this->getId(), $this->getSlug() ?: 'event');

        $data = [
            '@type' => 'Event',
            'identifier' => (string)$this->getId(),
            'name' => $this->getTitle(),
            'url' => $eventUrl,
            'startDate' => $this->getStartDate(),
            'endDate' => $this->getEndDate(),
            'timezone' => $this->getTimezone(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'category' => $this->getCategory(),
        ];

        $description = $this->getDescriptionPreview() ?: $this->getDescription();
        if (!empty($description)) {
            $data['description'] = trim(strip_tags((string)$description));
        }

        $location = $this->getLocationDetails();
        if (!empty($location)) {
            $data['location'] = $location;
        }

        if ($this->getImages() && $this->getImages()->isNotEmpty()) {
            $data['image'] = Url::getCdnUrl($this->getImages()->first()->getPath());
        }

        if ($this->getOrganizer()) {
            $organizer = $this->getOrganizer();
            $data['organizer'] = [
                '@type' => 'Organization',
                'name' => $organizer->getName(),
                'url' => $publicUrl . sprintf('/events/%d/%s', $organizer->getId(), $organizer->getSlug() ?: 'organizer'),
            ];
        }

        $data['offers'] = [
            '@type' => 'AggregateOffer',
            'priceCurrency' => $this->getCurrency(),
            'url' => $eventUrl,
        ];

        return $data;
    }
}
