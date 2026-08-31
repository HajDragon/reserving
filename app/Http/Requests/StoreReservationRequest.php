<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Normalize timezone-offset input (e.g. "+02:00") to UTC before validation,
     * so overlap checks and stored values are always on the app timezone.
     */
    protected function prepareForValidation(): void
    {
        foreach (['start_time', 'end_time'] as $field) {
            $value = $this->input($field);

            if (! is_string($value) || $value === '') {
                continue;
            }

            try {
                $this->merge([$field => Carbon::parse($value)->utc()->format('Y-m-d H:i:s')]);
            } catch (\Throwable) {
                // Leave the raw value so the 'date' rule returns a validation error.
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'reserved_quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
