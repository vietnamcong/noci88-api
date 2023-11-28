<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class Language
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->guard('member')->check() && auth()->guard('member')->user()->lang != null) {
            // Set the application locale to the authenticated user's preferred language
            $this->app->setLocale(auth()->guard('member')->user()->lang);
        } elseif ($request->hasHeader('Accept-Language')) {
            // Extract and use the first language from the Accept-Language header
            $acceptLanguage = $request->header('Accept-Language');
            $languages = explode(',', $acceptLanguage);
            $locale = substr($languages[0], 0, 2); // Get the first two characters of the language code
            $this->app->setLocale($locale);
        } else {
            // Set the application locale to the language prefix saved in session or default to 'vi'
            $this->app->setLocale(session()->get('language_prefix', 'vi'));
        }

        return $next($request);
    }
}