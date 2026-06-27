<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use Illuminate\Contracts\View\View;

class WelcomePageController
{
    public function __invoke(): View
    {
        return view('welcome-app', [
            'contactInfo' => ContactInfo::instance(),
        ]);
    }
}
