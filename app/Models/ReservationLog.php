<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Database\Factories\ReservationLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationLog extends Model
{
    /** @use HasFactory<ReservationLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'reservation_id',
        'reservation_order_id',
        'user_id',
        'product_id',
        'product_name',
        'reserved_quantity',
        'start_time',
        'end_time',
        'extra_wishes',
        'status',
        'returned_at',
        'returned_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'returned_at' => 'datetime',
            'reserved_quantity' => 'integer',
            'status' => ReservationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
