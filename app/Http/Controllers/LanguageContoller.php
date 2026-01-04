<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $locale = $request->input('language');
        
        // Validate the locale using Laravel Localization package's supported locales
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales'));
        
        if (in_array($locale, $supportedLocales)) {
            Session::put('locale', $locale);
        }
        
        return redirect()->back();
    }
}