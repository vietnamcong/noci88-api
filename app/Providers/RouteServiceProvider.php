<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        parent::boot();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Hàm này xác định cách giới hạn tốc độ truy cập cho nhóm route 'member'
        // Limit::perMinute(60): Cho phép 60 requests mỗi phút
        // optional($request->user())->id ?: $request->ip(): Xác định xem requests đến từ một người dùng (nếu đã đăng nhập) hay từ một địa chỉ IP
        RateLimiter::for('member', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    public function map()
    {
        $this->mapWebRoutes();
        $this->mapClientPCRoutes();
        $this->mapApiRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes()
    {
        Route::middleware('web')
            ->prefix('/api')
            ->namespace($this->namespace)
            ->group(base_path('routes/game.php'));
    }

    protected function mapClientPCRoutes()
    {
        Route::prefix(env('PREFIX_ROUTE_CLIENT_ALIAS', 'api/client-pc/v1'))
            ->middleware('member')
            ->namespace($this->namespace . '\API\ClientPC')
            ->group(base_path('routes/client-pc.php'));
    }
}
