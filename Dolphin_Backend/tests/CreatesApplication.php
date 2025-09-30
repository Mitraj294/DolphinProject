<?php
// phpcs:ignoreFile

namespace Tests;

use App\Console\Kernel as ConsoleKernel;
use App\Exceptions\Handler;
use App\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Facade;


trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $basePath = dirname(__DIR__);

        $app = Application::configure(basePath: $basePath)
            ->withRouting(
                web: $basePath . '/routes/web.php',
                api: $basePath . '/routes/api.php',
                commands: $basePath . '/routes/console.php',
                health: '/up',
            )
            ->withMiddleware(static function (Middleware $middleware): void {
                // No global middleware overrides for tests.
                unset($middleware);
            })
            ->withExceptions(static function (Exceptions $exceptions): void {
                // Default exception handling configuration is sufficient here.
                unset($exceptions);
            })
            ->create();

        Application::setInstance($app);
        Facade::setFacadeApplication($app);

        $app->singleton(ConsoleKernelContract::class, ConsoleKernel::class);
        $app->singleton(HttpKernelContract::class, HttpKernel::class);
        $app->singleton(ExceptionHandler::class, Handler::class);

        $app->make(ConsoleKernelContract::class)->bootstrap();

        return $app;
    }
}
