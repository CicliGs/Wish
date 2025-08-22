<?php

namespace App\Services;

enum CacheType: string
{
    case STATIC_CONTENT = 'static_content';
    case IMAGES = 'images';
    case CSS_JS = 'css_js';
    case AVATARS = 'avatars';

    public function getTTL(): int
    {
        return match ($this) {
            self::STATIC_CONTENT => 86400,      // 24 hours
            self::IMAGES, self::AVATARS => 604800,              // 7 days
            self::CSS_JS => 2592000,             // 30 days
        };
    }

    public function getPrefix(): string
    {
        return match ($this) {
            self::STATIC_CONTENT => 'static_content',
            self::IMAGES => 'image',
            self::CSS_JS => 'asset',
            self::AVATARS => 'avatar',
        };
    }
}
