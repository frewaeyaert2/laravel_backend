<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the Accept-Language header from the request
        $acceptLanguage = $request->header('Accept-Language');

        // turn accept language string into array of languages and take the first one
        $preferredLanguages = explode(',', $acceptLanguage);
        $preferredLanguage = strtolower(trim($preferredLanguages[0]));

        // Retrieve the allowed locales from the configuration
        $allowedLocales = array_keys(Config::get('locales.languages'));

        if (in_array($preferredLanguage, $allowedLocales)) {
            app()->setLocale($preferredLanguage);
        } else {
            app()->setLocale('nl');
        }

        return $next($request);
    }
}
