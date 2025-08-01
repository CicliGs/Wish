<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Переключение языка.
     */
    public function switchLanguage(Request $request, string $locale)
    {
        $supportedLocales = ['en', 'ru'];
        
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'ru';
        }        
        Session::put('locale', $locale);
        App::setLocale($locale);
        
        return redirect()->back();
    }
} 