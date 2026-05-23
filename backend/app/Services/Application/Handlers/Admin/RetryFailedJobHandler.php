<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Models\FailedJob;
use Illuminate\Support\Facades\Artisan;

class RetryFailedJobHandler
{
    public function handle(int $id): bool
    {
        $job = FailedJob::find($id);

        if (!$job) {
            return false;
        }

        Artisan::call('queue:retry', ['id' => [$job->uuid]]);

        return true;
    }

    public function retryAll(): int
    {
        $count = FailedJob::count();

        if ($count > 0) {
            Artisan::call('queue:retry', ['id' => ['all']]);
        }

        return $count;
    }
}
