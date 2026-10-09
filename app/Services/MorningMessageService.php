<?php

namespace App\Services;

use App\Models\BibleMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MorningMessageService
{
    /**
     * Publish every scheduled message whose publish_date is today.
     *
     * Safe to run more than once a day: only messages still in the
     * 'scheduled' state are picked up, so a message is never published
     * twice. Returns the messages that were published this run, so a
     * notification step can use them later.
     *
     * @return Collection<int, BibleMessage>
     */
    public function publishDueMessages(?Carbon $today = null): Collection
    {
        $today = ($today ?? Carbon::today())->toDateString();

        return DB::transaction(function () use ($today) {
            $due = BibleMessage::where('status', 'scheduled')
                ->whereDate('publish_date', $today)
                ->lockForUpdate()
                ->get();

            foreach ($due as $message) {
                $message->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);
            }

            return $due;
        });
    }
}