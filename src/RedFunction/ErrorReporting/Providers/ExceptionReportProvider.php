<?php
namespace RedFunction\ErrorReporting\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use RedFunction\ErrorReporting\ExceptionReportHandler;

/**
 * Class ExceptionReportProvider
 *
 * @package RedFunction\ErrorReporting\Providers
 */
class ExceptionReportProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Package boot method
     */
    public function boot()
    {

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            ExceptionHandler::class,
            ExceptionReportHandler::class
        );
    }
}