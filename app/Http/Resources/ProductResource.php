<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_tag' => $this->asset_tag,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'is_active' => $this->is_active,
            'photo_path' => $this->photo_path,
            'external_link' => $this->external_link,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
