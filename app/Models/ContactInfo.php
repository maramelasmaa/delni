<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = [
        'whatsapp',
        'phone',
        'email',
        'address',
        'facebook',
        'welcome_badge',
        'welcome_title',
        'welcome_subtitle',
        'ios_app_url',
        'android_app_url',
    ];

    public static function instance(): self
    {
        return self::first() ?? self::create();
    }
}
