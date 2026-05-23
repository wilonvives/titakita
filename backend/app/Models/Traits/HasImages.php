<?php

namespace TitaKita\Models\Traits;

use TitaKita\Models\Image;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasImages
{
    public function images(): MorphMany
    {
        return $this->morphMany(related: Image::class, name: 'entity');
    }
}
