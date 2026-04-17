<?php

namespace App\Http\Requests;

use App\Enums\AdminReservationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReviewReservationRequest extends FormRequest
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
        return [
            'status' => ['required', Rule::in(array_map(
                static fn (AdminReservationStatus $status): string => $status->value,
                AdminReservationStatus::cases(),
            ))],
            'start_time' => ['nullable', 'date', 'after:now'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'reserved_quantity' => ['nullable', 'integer', 'min:1'],
            'extra_wishes' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $status = $this->string('status')->toString();

                if ($status === AdminReservationStatus::Rejected->value && ! $this->filled('rejection_reason')) {
                    $validator->errors()->add('rejection_reason', 'A rejection reason is required when rejecting a reservation.');
                }

                if ($status === AdminReservationStatus::Approved->value) {
                    $hasStartTime = $this->filled('start_time');
                    $hasEndTime = $this->filled('end_time');

                    if ($hasStartTime xor $hasEndTime) {
                        $validator->errors()->add('start_time', 'Start and end time must be provided together when editing an approval.');
                    }
                }
            },
        ];
    }
}
