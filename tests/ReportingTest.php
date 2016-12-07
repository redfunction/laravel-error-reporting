<?php

namespace RedFunction\ErrorReporting\Tests;

use DateTime;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Routing\Router;
use Mockery;
use RedFunction\ErrorReporting\Examples\ExceptionUsingReport;
use RedFunction\ErrorReporting\ExceptionReportHandler;


class ReportingTest extends TestCase {

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The base URL to use while testing the application.
     *
     * @var \Mockery\MockInterface|Router
     */
    protected $mockRouter;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = new Application(
            realpath(__DIR__ . '/../')
        );
        $app->singleton(
            HttpKernelContract::class,
            HttpKernel::class
        );
        $app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class
        );
        $app->singleton(
            ConsoleKernelContract::class,
            ConsoleKernel::class
        );
        $app->singleton(ExceptionHandler::class,
            ExceptionReportHandler::class);
        $this->baseUrl = env('APP_URL');
        return $app;
    }

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->mockRouter = Mockery::mock(Router::class);
        $this->app->instance(Router::class, $this->mockRouter);
    }

    public function testErrorReportingFlow(){
        $logFile = __DIR__ . '/../storage/logs/' . 'laravel-' . (new DateTime())->format('Y-m-d') . '.log';
        if(file_exists($logFile)) @unlink($logFile);
        $this->mockRouter->shouldReceive("dispatch")->once()->andThrow(new ExceptionUsingReport());
        $this->call("GET", "/");
        $this->assertTrue(file_exists($logFile), 'Log file is missing');
        $logData = file_get_contents($logFile);
        $this->assertTrue(strpos($logData, 'Error 500: reason...') !== false, 'Not wrote to log file. \'Error 500: reason...\'');
    }
}