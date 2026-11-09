<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:выполнена,в процессе,не выполнена',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название задачи не может быть пустым.',
            'due_date.required' => 'Укажите срок выполнения.',
        ];
    }
}
