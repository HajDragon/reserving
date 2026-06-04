<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateManagedProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-reserving-dashboard') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'asset_tag' => ['required', 'string', 'max:255', Rule::unique('products', 'asset_tag')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'external_link' => ['nullable', 'url', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $externalLink = trim((string) $this->input('external_link', ''));

        if ($externalLink !== '' && ! Str::startsWith($externalLink, ['http://', 'https://'])) {
            $externalLink = 'https://'.$externalLink;
        }

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'external_link' => $externalLink !== '' ? $externalLink : null,
        ]);
    }
}
