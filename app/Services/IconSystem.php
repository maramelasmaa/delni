<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\HtmlString;

class IconSystem
{
    const FALLBACK_ICON = 'heroicon-o-square-3-stack-3d';

    const DEFAULT_SIZE = 'w-5 h-5';

    const DEFAULT_COLOR = 'text-gray-700';

    private static array $validIconCache = [];

    public static function render(?string $icon, string $size = self::DEFAULT_SIZE, string $color = self::DEFAULT_COLOR, array $attributes = []): HtmlString
    {
        if (empty($icon)) {
            return self::renderHeroicon(self::FALLBACK_ICON, $size, $color, $attributes);
        }

        // Validate and render Heroicon
        if (self::isValidHeroicon($icon)) {
            return self::renderHeroicon($icon, $size, $color, $attributes);
        }

        // Invalid icon — use fallback
        return self::renderHeroicon(self::FALLBACK_ICON, $size, $color, $attributes);
    }

    public static function renderHeroicon(string $icon, string $size = self::DEFAULT_SIZE, string $color = self::DEFAULT_COLOR, array $attributes = []): HtmlString
    {
        $class = trim("{$size} {$color} ".($attributes['class'] ?? ''));
        $attributes['class'] = $class;

        $attrString = self::buildAttributes($attributes);

        // Convert heroicon-o-palette to heroicon.o-palette for blade component syntax
        // Only convert the first hyphen between namespace (heroicon) and style (o/s)
        $componentName = preg_replace('/^(heroicon)-/', '$1.', $icon);

        // Render the blade component using Blade::render()
        $html = \Blade::render("<x-{$componentName} {$attrString} />");

        return new HtmlString($html);
    }

    public static function isValidHeroicon(string $icon): bool
    {
        if (isset(self::$validIconCache[$icon])) {
            return self::$validIconCache[$icon];
        }

        $pattern = '/^heroicon-(o|s)-[\w\-]+$/';
        $isValid = (bool) preg_match($pattern, $icon);

        self::$validIconCache[$icon] = $isValid;

        return $isValid;
    }

    private static function buildAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $parts[] = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            } else {
                $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
                $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                $parts[] = "{$key}=\"{$value}\"";
            }
        }

        return implode(' ', $parts);
    }

    public static function getHeroiconsList(): array
    {
        return self::getOutlineIcons();
    }

    private static function getOutlineIcons(): array
    {
        return [
            // Core System Icons
            'heroicon-o-home' => 'Home',
            'heroicon-o-squares-2x2' => 'Dashboard',
            'heroicon-o-cog-6-tooth' => 'Settings',
            'heroicon-o-bell' => 'Notifications',
            'heroicon-o-envelope' => 'Mail',
            'heroicon-o-search' => 'Search',

            // User & Profile
            'heroicon-o-user-circle' => 'User Profile',
            'heroicon-o-users' => 'Users',
            'heroicon-o-user-plus' => 'Add User',
            'heroicon-o-identification' => 'Identity',
            'heroicon-o-shield-check' => 'Verified',
            'heroicon-o-lock-closed' => 'Lock',
            'heroicon-o-key' => 'Password/Key',

            // Content & Organization
            'heroicon-o-folder' => 'Folder/Category',
            'heroicon-o-folder-plus' => 'Add Category',
            'heroicon-o-tag' => 'Tag/Subcategory',
            'heroicon-o-document' => 'Document',
            'heroicon-o-document-text' => 'Text Document',
            'heroicon-o-clipboard-document-list' => 'Activity Log',
            'heroicon-o-library' => 'Library',

            // Business & Commerce
            'heroicon-o-briefcase' => 'Business/Work',
            'heroicon-o-credit-card' => 'Payment/Card',
            'heroicon-o-shopping-cart' => 'Cart',
            'heroicon-o-shopping-bag' => 'Shopping',
            'heroicon-o-currency-dollar' => 'Dollar',
            'heroicon-o-banknotes' => 'Money',
            'heroicon-o-hand-raised' => 'Offer/Raise Hand',

            // Location & Map
            'heroicon-o-map-pin' => 'Location/City',
            'heroicon-o-map' => 'Map',
            'heroicon-o-globe-alt' => 'Global',
            'heroicon-o-building-office' => 'Office/Building',
            'heroicon-o-building-storefront' => 'Store',

            // Communications
            'heroicon-o-phone' => 'Phone',
            'heroicon-o-chat-bubble-left' => 'Message',
            'heroicon-o-chat-bubble-left-ellipsis' => 'Chat',
            'heroicon-o-megaphone' => 'Announce',
            'heroicon-o-speaker-wave' => 'Volume/Sound',

            // Media & Content
            'heroicon-o-photo' => 'Photo/Portfolio',
            'heroicon-o-video-camera' => 'Video',
            'heroicon-o-camera' => 'Camera',
            'heroicon-o-film' => 'Film',
            'heroicon-o-image' => 'Image',
            'heroicon-o-images' => 'Gallery',
            'heroicon-o-eye' => 'View/Visible',
            'heroicon-o-eye-slash' => 'Hidden',

            // Status & Feedback
            'heroicon-o-star' => 'Rating/Review',
            'heroicon-o-heart' => 'Like/Favorite',
            'heroicon-o-hand-thumbs-up' => 'Good/Approve',
            'heroicon-o-hand-thumbs-down' => 'Bad/Reject',
            'heroicon-o-check-circle' => 'Success/Active',
            'heroicon-o-x-circle' => 'Error/Inactive',
            'heroicon-o-exclamation-circle' => 'Warning',
            'heroicon-o-information-circle' => 'Info',
            'heroicon-o-check' => 'Check/Approve',
            'heroicon-o-x-mark' => 'Close/Reject',
            'heroicon-o-flag' => 'Flag/Mark',

            // Services & Skills
            'heroicon-o-wrench-screwdriver' => 'Maintenance/Tools',
            'heroicon-o-hammer' => 'Repair',
            'heroicon-o-puzzle-piece' => 'Service',
            'heroicon-o-sparkles' => 'Feature/Premium',
            'heroicon-o-bolt' => 'Power/Energy',
            'heroicon-o-fire' => 'Hot/Trending',

            // Health & Wellness
            'heroicon-o-heart' => 'Health/Medical',
            'heroicon-o-hand-raised' => 'Care/Support',
            'heroicon-o-bandage' => 'Medical',

            // Education & Learning
            'heroicon-o-academic-cap' => 'Education/Credential',
            'heroicon-o-book-open' => 'Learning',
            'heroicon-o-pencil' => 'Edit/Write',
            'heroicon-o-pencil-square' => 'Edit',

            // Transport & Logistics
            'heroicon-o-truck' => 'Delivery/Logistics',
            'heroicon-o-car' => 'Auto/Vehicle',
            'heroicon-o-arrow-up-tray' => 'Upload/Delivery Out',
            'heroicon-o-arrow-down-tray' => 'Download/Delivery In',

            // Creative & Design
            'heroicon-o-palette' => 'Design/Creative',
            'heroicon-o-paint-brush' => 'Design/Art',
            'heroicon-o-code-bracket' => 'Code/Tech',
            'heroicon-o-computer-desktop' => 'Desktop/Tech',
            'heroicon-o-cpu-chip' => 'Processor/Tech',

            // Time & Scheduling
            'heroicon-o-calendar' => 'Date/Schedule',
            'heroicon-o-calendar-days' => 'Calendar',
            'heroicon-o-clock' => 'Time/Waiting',
            'heroicon-o-hourglass' => 'Loading/Time',

            // Navigation & Controls
            'heroicon-o-arrow-left' => 'Back',
            'heroicon-o-arrow-right' => 'Next',
            'heroicon-o-arrow-up' => 'Up',
            'heroicon-o-arrow-down' => 'Down',
            'heroicon-o-arrow-path' => 'Refresh/Retry',
            'heroicon-o-arrow-top-right-on-square' => 'Open/External',
            'heroicon-o-bars-3' => 'Menu',
            'heroicon-o-plus' => 'Add/New',
            'heroicon-o-minus' => 'Remove/Delete',

            // Security & Admin
            'heroicon-o-shield-check' => 'Security/Verified',
            'heroicon-o-no-symbol' => 'Prohibition/Suspend',
            'heroicon-o-funnel' => 'Filter',
            'heroicon-o-funnel-minus' => 'Clear Filter',
            'heroicon-o-trash' => 'Delete',
            'heroicon-o-archive-box' => 'Archive',

            // Miscellaneous
            'heroicon-o-ellipsis-horizontal' => 'More Options',
            'heroicon-o-ellipsis-vertical' => 'Actions',
            'heroicon-o-square-3-stack-3d' => 'Fallback/Stack',
        ];
    }
}
