<?php

namespace App\Models;

use Database\Factories\ReturnedReservationLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ReturnedReservationLog extends Model
{
    /** @use HasFactory<ReturnedReservationLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reservation_id',
        'reservation_order_id',
        'product_id',
        'user_id',
        'product_name',
        'quantity',
        'returned_at',
        'reservation_start_time',
        'reservation_end_time',
        'extra_wishes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'returned_at' => 'datetime',
            'reservation_start_time' => 'datetime',
            'reservation_end_time' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearchProductName(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        return $query->where('product_name', 'like', '%'.$search.'%');
    }

    public function scopeSortByReturnedDate(Builder $query, string $direction = 'desc'): Builder
    {
        $normalizedDirection = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('returned_at', $normalizedDirection)->orderByDesc('id');
    }

    public function scopeFilterByReturnedWeekday(Builder $query, ?int $weekday): Builder
    {
        if (! is_int($weekday) || $weekday < 1 || $weekday > 7) {
            return $query;
        }

        return $query->whereRaw($this->weekdaySql('returned_at').' = ?', [$weekday]);
    }

    private function weekdaySql(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => 'EXTRACT(ISODOW FROM '.$column.')',
            'sqlite' => '((CAST(strftime("%w", '.$column.') AS integer) + 6) % 7) + 1',
            default => 'WEEKDAY('.$column.') + 1',
        };
    }
}
