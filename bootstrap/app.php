<?php

require_once __DIR__.'/../vendor/autoload.php';

use Dusterio\LumenPassport\LumenPassport;

try {
    (new Dotenv\Dotenv(dirname(__DIR__)))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// Enable Facades
$app->withFacades();

// Enable Eloquent
$app->withEloquent();

// Enable auth middleware (shipped with Lumen)
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
    'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class
]);



/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);



/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Appzcoder\LumenRoutesList\RoutesCommandServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Linfo\Laravel\LinfoServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Ixudra\Curl\CurlServiceProvider::class);
$app->register(Nexmo\Laravel\NexmoServiceProvider::class);
$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);
$app->register(Propaganistas\LaravelPhone\PhoneServiceProvider::class);
$app->register(Codenexus\GeoIP\GeoIPServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);

$app->configure('auth');
$app->configure('swagger-lume');
$app->configure('broadcasting');
$app->configure('filesystems');

//Mail Configurate
$app->configure('mail');
$app->configure('services');
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('Curl', Ixudra\Curl\Facades\Curl::class);



if (env('APP_DEBUG')) {
    $app->register(Barryvdh\Debugbar\LumenServiceProvider::class);
}

if (env('APP_ENV') === 'local') {
    $app->bind(Illuminate\Database\ConnectionResolverInterface::class, Illuminate\Database\ConnectionResolver::class);
    $app->register(Niellles\LumenCommands\LumenCommandsServiceProvider::class);
}

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

LumenPassport::allowMultipleTokens();

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

// app('translator')->setLocale('il');

return $app;
