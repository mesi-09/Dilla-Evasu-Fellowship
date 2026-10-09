<?php

namespace Tests\Feature;

use App\Models\BibleMessage;
use App\Services\MorningMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MorningMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeMessage(string $status, ?string $date, string $title = 'A message'): BibleMessage
    {
        return BibleMessage::create([
            'title' => $title,
            'verse' => 'John 3:16',
            'message' => 'For God so loved the world.',
            'publish_date' => $date,
            'status' => $status,
        ]);
    }

    public function test_message_scheduled_for_today_is_published(): void
    {
        $message = $this->makeMessage('scheduled', Carbon::today()->toDateString());

        $published = (new MorningMessageService())->publishDueMessages();

        $this->assertCount(1, $published);
        $this->assertDatabaseHas('bible_messages', [
            'id' => $message->id,
            'status' => 'published',
        ]);
        $this->assertNotNull($message->fresh()->published_at);
    }

    public function test_message_scheduled_for_a_future_day_is_not_published(): void
    {
        $message = $this->makeMessage('scheduled', Carbon::tomorrow()->toDateString());

        $published = (new MorningMessageService())->publishDueMessages();

        $this->assertCount(0, $published);
        $this->assertSame('scheduled', $message->fresh()->status);
    }

    public function test_draft_message_is_never_published(): void
    {
        $message = $this->makeMessage('draft', Carbon::today()->toDateString());

        (new MorningMessageService())->publishDueMessages();

        $this->assertSame('draft', $message->fresh()->status);
    }

    public function test_running_twice_does_not_publish_a_message_twice(): void
    {
        $this->makeMessage('scheduled', Carbon::today()->toDateString());
        $service = new MorningMessageService();

        $first = $service->publishDueMessages();
        $second = $service->publishDueMessages();

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
    }

    public function test_only_todays_message_is_published_from_a_weekly_queue(): void
    {
        $monday = $this->makeMessage('scheduled', '2026-10-05', 'Monday');
        $tuesday = $this->makeMessage('scheduled', '2026-10-06', 'Tuesday');
        $wednesday = $this->makeMessage('scheduled', '2026-10-07', 'Wednesday');

        $published = (new MorningMessageService())->publishDueMessages(Carbon::parse('2026-10-06'));

        $this->assertCount(1, $published);
        $this->assertSame('Tuesday', $published->first()->title);
        $this->assertSame('scheduled', $monday->fresh()->status);
        $this->assertSame('published', $tuesday->fresh()->status);
        $this->assertSame('scheduled', $wednesday->fresh()->status);
    }

    public function test_nothing_happens_when_no_messages_are_scheduled(): void
    {
        $published = (new MorningMessageService())->publishDueMessages();

        $this->assertCount(0, $published);
    }
}