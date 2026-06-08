<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $fillable = ['whatsapp', 'phone', 'email', 'address'];

    public static function instance(): self
    {
        return self::first() ?? self::create();
    }
}
