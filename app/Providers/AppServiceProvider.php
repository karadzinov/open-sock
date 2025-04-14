<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SlackWebhookHandler;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('Y-m-d H:m');
        });
    }

    public function boot(Request $request)
    {

        if(\Auth::check())
        {
            $lang = $request->headers->get('lang');
            app('translator')->setLocale($lang);
        }
        $log = new Logger('sapo');
     //   $handler = new SlackWebhookHandler('xoxp-478547737904-480685261574-515120873425-2d06b52a4932a706e51b55cfa596e06b','#sapo-errors', null, Logger::ERROR, null);
        // $log->pushHandler($handler);
        $log->pushHandler(new StreamHandler('sapo.log', Logger::WARNING));


        Schema::defaultStringLength(191);
    }
}
