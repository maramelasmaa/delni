<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\HtmlString;

class IconRenderer
{
    /**
     * Render an icon from various formats.
     *
     * Supports:
     * - Emojis: Direct unicode characters
     * - Heroicons: heroicon-o-name or heroicon-s-name format
     * - Font Icons: fi fi-name format
     * - Fallback: Default text/emoji
     */
    public static function render(?string $icon, string $default = '•', array $attributes = []): HtmlString
    {
        if (empty($icon)) {
            return new HtmlString(self::escapeHtml($default));
        }

        // Check if it's an emoji
        if (self::isEmoji($icon)) {
            return new HtmlString(self::escapeHtml($icon));
        }

        // Check for Heroicon format
        if (preg_match('/^heroicon-(o|s)-(.+)$/', $icon, $matches)) {
            return new HtmlString(self::renderHeroicon($matches[2], $matches[1], $attributes));
        }

        // Check for Font Icon format
        if (strpos($icon, 'fi') === 0) {
            return new HtmlString(self::renderFontIcon($icon, $attributes));
        }

        // Fallback: render as text/emoji
        return new HtmlString(self::escapeHtml($icon));
    }

    /**
     * Check if a string is an emoji.
     */
    private static function isEmoji(string $string): bool
    {
        // Check if string is short (likely an emoji)
        if (mb_strlen($string) > 2) {
            return false;
        }

        // Check for emoji unicode ranges
        return (bool) preg_match('/\p{So}|\p{Sk}|\p{Sc}/u', $string);
    }

    /**
     * Render a Heroicon SVG using blade-icons.
     *
     * Uses view() helper to dynamically render with blade-icons component system.
     */
    private static function renderHeroicon(string $name, string $style, array $attributes = []): string
    {
        $iconName = "heroicon-{$style}-{$name}";
        $class = $attributes['class'] ?? '';

        // Render using a Blade view that handles dynamic component rendering
        try {
            return view('components.heroicon-renderer', [
                'icon' => $iconName,
                'class' => $class,
            ])->render();
        } catch (\Exception $e) {
            // Fallback to emoji if rendering fails
            return '📦';
        }
    }

    /**
     * Render a Font Icon as an <i> element.
     */
    private static function renderFontIcon(string $iconClass, array $attributes = []): string
    {
        $class = htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8');
        $attrs = self::buildAttributes($attributes);

        return "<i class=\"{$class}\" {$attrs}></i>";
    }

    /**
     * Build HTML attributes string from array.
     */
    private static function buildAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $parts = [];
        foreach ($attributes as $key => $value) {
            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            $parts[] = "{$key}=\"{$value}\"";
        }

        return implode(' ', $parts);
    }

    /**
     * Safely escape HTML content.
     */
    private static function escapeHtml(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
