<?php

namespace App\Livewire;

use App\Models\CartItem;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CartItemForm extends Component
{
    public CartItem $cartItem;

    public $start_time;

    public $end_time;

    public $requested_quantity;

    public $extra_wishes;

    public $updateMessage = '';

    public $updateError = '';

    public function mount()
    {
        $this->start_time = $this->cartItem->start_time->format('Y-m-d\TH:i');
        $this->end_time = $this->cartItem->end_time->format('Y-m-d\TH:i');
        $this->requested_quantity = $this->cartItem->requested_quantity;
        $this->extra_wishes = $this->cartItem->extra_wishes;
    }

    #[Computed]
    public function product()
    {
        return $this->cartItem->product;
    }

    #[On('update-start-time')]
    public function updateStartTime()
    {
        $this->resetMessages();
        $this->syncChanges();
    }

    #[On('update-end-time')]
    public function updateEndTime()
    {
        $this->resetMessages();
        $this->syncChanges();
    }

    #[On('update-quantity')]
    public function updateQuantity()
    {
        $this->resetMessages();
        $this->syncChanges();
    }

    #[On('update-wishes')]
    public function updateWishes()
    {
        $this->resetMessages();
        $this->syncChanges();
    }

    public function messages(): array
    {
        return [
            'end_time.after' => __('Please select a valid start and end date, the end date comes before the start date.'),
        ];
    }

    protected function syncChanges()
    {
        $startCarbon = Carbon::parse($this->start_time);
        $endCarbon = Carbon::parse($this->end_time);

        if ($endCarbon->lte($startCarbon)) {
            $this->addError('end_time', __('Please select a valid start and end date, the end date comes before the start date.'));
            $this->dispatch('cart-item-validity-changed', itemId: $this->cartItem->id, valid: false);

            return;
        }

        try {
            $validated = $this->validate([
                'start_time' => 'required|date_format:Y-m-d\\TH:i',
                'end_time' => 'required|date_format:Y-m-d\\TH:i',
                'requested_quantity' => 'required|integer|min:1',
                'extra_wishes' => 'nullable|string|max:2000',
            ]);

            $this->cartItem->update([
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'requested_quantity' => $this->requested_quantity,
                'extra_wishes' => $this->extra_wishes ?? null,
            ]);

            $this->updateMessage = __('Cart item updated.');
            $this->dispatch('cart-updated');
            $this->dispatch('cart-item-validity-changed', itemId: $this->cartItem->id, valid: true);
        } catch (\Exception $e) {
            $this->updateError = __('Failed to update cart item.');
        }
    }

    protected function resetMessages()
    {
        $this->updateMessage = '';
        $this->updateError = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cart-item-form', [
            'product' => $this->product,
        ]);
    }
}
