<?php

namespace App\Enums;

enum ProfileHiddenReason: string
{
    case NO_USER = 'no_user';
    case USER_INACTIVE = 'user_inactive';
    case USER_SUSPENDED = 'user_suspended';
    case PROFILE_INCOMPLETE = 'profile_incomplete';
    case ACCESS_EXPIRED = 'access_expired';
}
