<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Поле "Название задачи" обязательно для заполнения.',
            'due_date.required' => 'Укажите срок выполнения задачи.',
            'due_date.date' => 'Некорректный формат даты.',
        ];
    }
}
