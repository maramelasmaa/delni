<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Chatbot is public
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:500'],
            'conversation_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'الرسالة مطلوبة',
            'message.max' => 'الرسالة يجب أن تكون أقل من 500 حرف',
        ];
    }
}
