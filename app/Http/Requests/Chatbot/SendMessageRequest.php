<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validated message request for chatbot V3.
 *
 * Ensures:
 * - Message is present and under 500 chars
 * - Conversation_id is valid format (prevents ID spoofing)
 * - Generates new conversation_id if missing
 */
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
            'conversation_id' => ['required', 'string', 'regex:/^chat_[a-f0-9]{32}$/'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'الرسالة مطلوبة',
            'message.max' => 'الرسالة يجب أن تكون أقل من 500 حرف',
            'conversation_id.required' => 'معرّف المحادثة مطلوب',
            'conversation_id.regex' => 'معرّف المحادثة غير صحيح',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني طويل جداً',
        ];
    }

    /**
     * Prepare input for validation.
     *
     * Generate conversation_id if missing (client just started).
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('conversation_id')) {
            $this->merge([
                'conversation_id' => 'chat_'.bin2hex(random_bytes(16)),
            ]);
        }
    }
}
