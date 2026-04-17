<?php

namespace App\Models;

use App\Actions\Reservations\AdjustProductInventoryAction;
use App\Enums\ReservationStatus;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'start_time',
        'end_time',
        'status',
        'reserved_quantity',
        'extra_wishes',
        'reservation_order_id',
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
            'status' => ReservationStatus::class,
            'returned_at' => 'datetime',
            'reserved_quantity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Reservation $reservation): void {
            app(AdjustProductInventoryAction::class)->deductForReservation($reservation);
        });
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

    public function reservationOrder(): BelongsTo
    {
        return $this->belongsTo(ReservationOrder::class);
    }

    /**
     * Safely transition the reservation status.
     */
    public function transitionTo(ReservationStatus $newStatus): bool
    {
        if ($this->status->canTransitionTo($newStatus)) {
            $this->status = $newStatus;

            return true;
        }

        return false;
    }
}
