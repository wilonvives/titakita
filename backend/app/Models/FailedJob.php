<?php

namespace TitaKita\Models;

class FailedJob extends BaseModel
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected function getCastMap(): array
    {
        return [];
    }
}
