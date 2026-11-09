<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'comment' => 'nullable|string|max:500',
        'items' => 'required|array|min:1',
        'items.*.dish_id' => 'required|exists:dishes,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];
}
}
