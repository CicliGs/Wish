<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Controller as BaseController;

final class LanguageController extends BaseController
{
    private const SUPPORTED_LOCALES = ['en', 'ru'];
    private const DEFAULT_LOCALE = 'ru';

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly Session $session,
        private readonly Application $app
    ) {}

    /**
     * Switch language.
     */
    public function switchLanguage(string $locale): RedirectResponse
    {
        $this->setLocale($this->validateLocale($locale));

        return redirect()->back();
    }

    /**
     * Validate and return supported locale.
     */
    private function validateLocale(string $locale): string
    {
        return in_array($locale, self::SUPPORTED_LOCALES)
            ? $locale
            : self::DEFAULT_LOCALE;
    }

    /**
     * Set locale in session and application.
     */
    private function setLocale(string $locale): void
    {
        $this->session->put('locale', $locale);
        $this->app->setLocale($locale);
    }
}
