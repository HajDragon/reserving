<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\AvailabilityService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'extra_wishes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Validate product quantity and remaining capacity.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $product = Product::query()->find($this->integer('product_id'));

                if ($product === null) {
                    return;
                }

                $requestedQuantity = $this->integer('requested_quantity');

                if ($requestedQuantity > $product->quantity) {
                    $validator->errors()->add('requested_quantity', 'The requested quantity exceeds the product quantity.');

                    return;
                }

                if (! app(AvailabilityService::class)->checkAvailability(
                    product: $product,
                    startTime: $this->input('start_time'),
                    endTime: $this->input('end_time'),
                    requestedQuantity: $requestedQuantity,
                )) {
                    $validator->errors()->add('requested_quantity', 'The selected time window does not have enough available units for this product.');
                }
            },
        ];
    }
}
