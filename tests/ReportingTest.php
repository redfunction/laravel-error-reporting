<?php

namespace RedFunction\ErrorReporting\Tests;

use DateTime;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use Mockery;
use Orchestra\Testbench\TestCase;
use RedFunction\ErrorReporting\Examples\ExceptionNotUsingReport;
use RedFunction\ErrorReporting\Examples\ExceptionUsingReport;
use RedFunction\ErrorReporting\ExceptionReportHandler;


/**
 * Class ReportingTest
 * @package RedFunction\ErrorReporting\Tests
 */
class ReportingTest extends TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $mockRouter;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->mockRouter = Mockery::mock(Router::class);
        # Don't care about these 2 calls
        $this->mockRouter->shouldReceive('middlewareGroup');
        $this->mockRouter->shouldReceive('aliasMiddleware');

        $this->app->instance(Router::class, $this->mockRouter);
        $this->app->singleton(ExceptionHandler::class, ExceptionReportHandler::class);
    }

    /**
     *
     */
    public function testErrorReportingFlow()
    {
        $logFile = storage_path('logs') . '/laravel-' . (new DateTime())->format('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $this->mockRouter->shouldReceive('dispatch')->once()->andThrow(new ExceptionUsingReport());
        $response = $this->call('GET', '/');
        $response->assertStatus(500);
        $this->assertFileExists($logFile, 'Log file is missing');
        $logData = file_get_contents($logFile);
        $this->assertNotFalse(strpos($logData, 'Error 500: reason...'), 'Log file not written to');

        $randomString = 'hakklihakeeks';
        $this->mockRouter->shouldReceive('dispatch')->once()
            ->andThrow(new ExceptionNotUsingReport($randomString, 523));
        $response = $this->call('GET', '/');
        $logData = file_get_contents($logFile);
        $this->assertNotFalse(strpos($logData, $randomString), 'Custom exception message not written to log');
        $response->assertStatus(500);
    }
}