<?php

namespace App\Console\Commands;

use App\Services\MorningMessageService;
use Illuminate\Console\Command;

class PublishMorningMessage extends Command
{
    protected $signature = 'messages:publish-morning';

    protected $description = 'Publish the Bible message scheduled for today';

    public function handle(MorningMessageService $service): int
    {
        $published = $service->publishDueMessages();

        if ($published->isEmpty()) {
            $this->info('No Bible message scheduled for today.');

            return self::SUCCESS;
        }

        foreach ($published as $message) {
            $this->info("Published: {$message->title} ({$message->verse})");
        }

        return self::SUCCESS;
    }
}