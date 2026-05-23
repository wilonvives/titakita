<?php

namespace TitaKita\DomainObjects;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrganizerDomainObject extends Generated\OrganizerDomainObjectAbstract
{
    private ?Collection $images = null;

    /**
     * @return Collection<EventDomainObject>|null
     */
    private ?Collection $events = null;

    private ?OrganizerSettingDomainObject $settings = null;

    public function getImages(): ?Collection
    {
        return $this->images;
    }

    public function setImages(?Collection $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getEvents(): ?Collection
    {
        return $this->events;
    }

    public function setEvents(?Collection $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function getOrganizerSettings(): ?OrganizerSettingDomainObject
    {
        return $this->settings;
    }

    public function setOrganizerSettings(?OrganizerSettingDomainObject $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSlug(): string
    {
        // Fall back to a non-empty slug for purely non-Latin names so organizer
        // URLs never collapse to /events/{id}/.
        return Str::slug($this->name) ?: ('organizer-' . $this->getId());
    }
}
