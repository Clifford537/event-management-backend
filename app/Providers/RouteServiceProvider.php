<?php

namespace App\Providers;

use App\Models\Event;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // Explicitly bind the 'event' route parameter to the Event model by 'id'
        Route::bind('event', function ($value) {
            return Event::where('id', $value)->firstOrFail();
        });
    }

    public function map()
    {
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
