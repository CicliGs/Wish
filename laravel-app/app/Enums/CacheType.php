<?php

declare(strict_types=1);

namespace App\Enums;

enum CacheType: string
{
    case STATIC_CONTENT = 'static_content';
    case IMAGES = 'images';
    case CSS_JS = 'css_js';
    case AVATARS = 'avatars';

    public function getTTL(): int
    {
        return match ($this) {
            self::STATIC_CONTENT => 86400,
            self::IMAGES, self::AVATARS => 604800,
            self::CSS_JS => 2592000,
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

