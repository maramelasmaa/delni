<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactInfo;

class ContactController extends Controller
{
    public function show()
    {
        $contactInfo = ContactInfo::first();

        return view('public.contact', ['contactInfo' => $contactInfo]);
    }
}
