<?php

namespace App\Console\Commands;

use App\Jobs\PollLiveMatchesJob;
use Illuminate\Console\Command;

class PollLiveMatchesCommand extends Command
{
    protected $signature = 'cricket:poll-live';

    protected $description = 'Poll live matches, warm Redis cache, and broadcast score updates';

    public function handle(): int
    {
        PollLiveMatchesJob::dispatchSync();
        $this->info('Live match poll completed.');

        return self::SUCCESS;
    }
}
