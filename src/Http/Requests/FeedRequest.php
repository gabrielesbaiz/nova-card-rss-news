<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Requests;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\SourceNotFound;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class FeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source' => ['nullable', 'string', 'max:191'],
            'feed' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fresh' => ['nullable', 'boolean'],
            'sources' => ['nullable', 'array', 'max:25'],
            'sources.*' => ['string', 'max:191'],
            'categories' => ['nullable', 'array', 'max:25'],
            'categories.*' => ['string', 'max:191'],
        ];
    }

    public function limit(): int
    {
        return (int) ($this->integer('limit') ?: config('nova-card-rss-news.defaults.limit', 10));
    }

    public function fresh(): bool
    {
        return $this->boolean('fresh');
    }

    /**
     * Resolve the requested source: either a catalogue key, or an ad-hoc feed
     * whose URL was signed into the card's meta. The encrypted form is the only
     * way to pass a raw URL, so a client cannot make us fetch arbitrary hosts.
     */
    public function source(SourceRepository $sources): Source
    {
        $encrypted = $this->string('feed')->toString();

        if ($encrypted !== '') {
            try {
                $payload = json_decode(Crypt::decryptString($encrypted), true, 512, JSON_THROW_ON_ERROR);
            } catch (DecryptException|\JsonException) {
                throw SourceNotFound::key('feed');
            }

            return Source::fromArray(
                (string) ($payload['key'] ?? 'inline'),
                is_array($payload) ? $payload : [],
                'inline',
            );
        }

        return $sources->findOrFail($this->string('source')->toString());
    }
}
