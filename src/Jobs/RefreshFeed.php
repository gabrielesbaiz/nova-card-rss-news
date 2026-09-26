<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Jobs;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Events\FeedFetchFailed;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Throwable;

/**
 * Background revalidation for a stale-but-usable cached feed.
 */
class RefreshFeed implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly Source $source) {}

    public function handle(FeedManager $feeds): void
    {
        try {
            $feeds->refresh($this->source);
        } catch (Throwable $e) {
            Event::dispatch(new FeedFetchFailed($this->source, $e, servedStale: true));
        }
    }

    public function uniqueId(): string
    {
        return $this->source->key;
    }
}
