<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'asset_tag',
        'name',
        'description',
        'category_id',
        'quantity',
        'available_quantity',
        'is_active',
        'external_link',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'quantity' => 1,
        'available_quantity' => 1,
        'is_active' => true,
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'photo_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getPhotoPathAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('photo');

        return $url ? parse_url($url, PHP_URL_PATH) : null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
