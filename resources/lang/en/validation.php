<?php

return [
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'numeric' => 'The :attribute must be a number.',
    'required' => 'The :attribute field is required.',
    'unique' => 'The :attribute has already been taken.',
    'email' => 'The :attribute must be a valid email address.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'custom' => [
        'email' => [
            'unique' => 'This email address is already registered.',
        ],
        'phone' => [
            'regex' => 'Please enter a valid phone number.',
        ],
        'whatsapp' => [
            'regex' => 'Enter the WhatsApp number in wa.me format: country code followed by the number, without +, spaces, or a leading local zero.',
        ],
        'password' => [
            'confirmed' => 'Password confirmation does not match.',
            'different' => 'New password must differ from your current password.',
            'symbols' => 'Password must contain special characters (!@#$%^&*).',
            'numbers' => 'Password must contain numbers.',
            'mixed' => 'Password must contain both uppercase and lowercase letters.',
        ],
        'current_password' => [
            'current_password' => 'The provided current password is incorrect.',
        ],
        'slug' => [
            'regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
            'regex_example' => 'Slug may only contain lowercase letters, numbers, and hyphens (e.g. real-estate).',
            'immutable' => 'Slugs are immutable after creation and cannot be changed.',
        ],
    ],
    'attributes' => [
        'phone' => 'phone',
        'whatsapp' => 'WhatsApp',
    ],
];
