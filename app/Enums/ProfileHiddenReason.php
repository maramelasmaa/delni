<?php

namespace App\Enums;

enum ProfileHiddenReason: string
{
    case NO_USER = 'no_user';
    case USER_INACTIVE = 'user_inactive';
    case USER_SUSPENDED = 'user_suspended';
    case PROFILE_INCOMPLETE = 'profile_incomplete';
    case NO_ACTIVE_SUBSCRIPTION = 'no_active_subscription';
    case SUBSCRIPTION_EXPIRED = 'subscription_expired';
}
