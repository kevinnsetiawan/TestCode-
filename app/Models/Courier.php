<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    public const MIN_LEVEL = 1;

    public const MAX_LEVEL = 5;

    public const VEHICLE_TYPES = [
        'bicycle',
        'motorcycle',
        'car',
        'van',
        'truck',
    ];

    public const STATUSES = [
        'active',
        'inactive',
        'on_leave',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'level',
        'vehicle_type',
        'license_plate',
        'service_area',
        'status',
        'registered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'registered_at' => 'datetime',
        ];
    }

    /**
     * Ensure registered_at and status always have sensible defaults on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (Courier $courier): void {
            $courier->registered_at ??= now();
            $courier->status ??= 'active';
        });
    }

    /**
     * Filter couriers by multi-word search query (e.g. "budi agung" matches "Budiono Hadi Agung").
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($terms as $term) {
            $escaped = addcslashes(mb_strtolower($term), '%_\\');
            $pattern = "%{$escaped}%";

            $query->where(function (Builder $subQuery) use ($pattern): void {
                $subQuery->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(code) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(service_area) LIKE ?', [$pattern]);
            });
        }

        return $query;
    }

    /**
     * Filter couriers by one or more levels (e.g. "2,3" or [2, 3]).
     *
     * @param  array<int|string>|string|null  $levels
     */
    public function scopeLevels(Builder $query, array|string|null $levels): Builder
    {
        if ($levels === null || $levels === '' || $levels === []) {
            return $query;
        }

        $raw = is_array($levels) ? $levels : explode(',', $levels);

        $parsed = collect($raw)
            ->map(fn (mixed $item): int => (int) trim((string) $item))
            ->filter(fn (int $level): bool => $level >= self::MIN_LEVEL && $level <= self::MAX_LEVEL)
            ->unique()
            ->values()
            ->all();

        if ($parsed === []) {
            return $query;
        }

        return $query->whereIn('level', $parsed);
    }

    /**
     * Sort couriers by name (default asc) or registered_at (default desc when direction omitted).
     */
    public function scopeSortedBy(Builder $query, ?string $sortBy = null, ?string $sortDir = null): Builder
    {
        $column = match ($sortBy) {
            'registered_at', 'created_at' => 'registered_at',
            default => 'name',
        };

        $direction = strtolower((string) $sortDir);
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $column === 'registered_at' ? 'desc' : 'asc';
        }

        return $query->orderBy($column, $direction)->orderBy('id', 'asc');
    }
}
