<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable([
    'source_type',
    'source_path',
    'what_this_is',
    'does',
    'does_not',
    'steps_on',
    'speaker',
    'private',
    'voice_line',
    'body',
])]
#[Hidden(['body', 'search_text'])]
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'private' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Card $card): void {
            $card->search_text = $card->composeSearchText();
        });
    }

    /**
     * Words a person would actually listen for. Drops glue so "is this lexi" still finds Lexi.
     *
     * @return Collection<int, string>
     */
    public static function terms(string $question): Collection
    {
        $stop = [
            'the', 'and', 'for', 'this', 'that', 'what', 'how', 'would', 'should',
            'could', 'with', 'from', 'have', 'does', 'not', 'are', 'was', 'you',
            'your', 'about', 'just', 'like', 'really', 'tell', 'someone',
        ];

        return collect(preg_split('/[^\p{L}\p{N}]+/u', Str::lower($question)) ?: [])
            ->map(fn (string $term): string => trim($term))
            ->filter(fn (string $term): bool => Str::length($term) >= 3 && ! in_array($term, $stop, true))
            ->unique()
            ->values();
    }

    /**
     * @return Collection<int, Card>
     */
    public static function pull(string $query, int $limit = 8): Collection
    {
        return static::query()
            ->matchingAny($query)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Card>
     */
    public static function pocket(string $question, int $limit = 6): Collection
    {
        return static::pull($question, $limit);
    }

    /**
     * How a person would actually say this stamp. Not a citation.
     */
    public function asSpoken(): string
    {
        $line = $this->what_this_is.': '.$this->does.'. Not: '.$this->does_not.'. Feet: '.$this->steps_on.'.';

        if (filled($this->voice_line)) {
            $line .= ' I already said: '.$this->voice_line;
        }

        return $line;
    }

    /**
     * Shape a card for tools. Body stays in SQL; tools pull the stamp, not the dump.
     *
     * @return array<string, mixed>
     */
    public function toPullArray(bool $withBody = false): array
    {
        $payload = [
            'id' => $this->id,
            'what_this_is' => $this->what_this_is,
            'does' => $this->does,
            'does_not' => $this->does_not,
            'steps_on' => $this->steps_on,
            'speaker' => $this->speaker,
            'voice_line' => $this->voice_line,
            'source_type' => $this->source_type,
            'private' => $this->private,
        ];

        if ($withBody) {
            $payload['body'] = Str::limit((string) $this->body, 1500);
        }

        return $payload;
    }

    public function composeSearchText(): string
    {
        return collect([
            $this->what_this_is,
            $this->does,
            $this->does_not,
            $this->steps_on,
            $this->speaker,
            $this->voice_line,
            $this->source_type,
            $this->source_path,
        ])->filter(fn (mixed $value): bool => filled($value))->implode(' ');
    }

    #[Scope]
    protected function matching(Builder $query, string $needle): Builder
    {
        return $query->matchingAny($needle);
    }

    #[Scope]
    protected function matchingAny(Builder $query, string $question): Builder
    {
        $terms = static::terms($question);

        if ($terms->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where(function (Builder $inner) use ($terms): void {
            foreach ($terms as $term) {
                $inner->orWhere('search_text', 'like', '%'.addcslashes($term, '%_\\').'%');
            }
        });
    }

    #[Scope]
    protected function quoting(Builder $query, string $needle): Builder
    {
        $terms = static::terms($needle);

        if ($terms->isEmpty()) {
            $like = '%'.addcslashes($needle, '%_\\').'%';

            return $query->where('voice_line', 'like', $like);
        }

        return $query->where(function (Builder $inner) use ($terms): void {
            foreach ($terms as $term) {
                $inner->orWhere('voice_line', 'like', '%'.addcslashes($term, '%_\\').'%');
            }
        });
    }
}
