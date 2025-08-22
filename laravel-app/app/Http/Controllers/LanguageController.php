<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    private const SUPPORTED_LOCALES = ['en', 'ru'];
    private const DEFAULT_LOCALE = 'ru';

    /**
     * Switch language.
     */
    public function switchLanguage(string $locale): RedirectResponse
    {
        $locale = $this->validateLocale($locale);

        $this->setLocale($locale);

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
        Session::put('locale', $locale);
        App::setLocale($locale);
    }
}
