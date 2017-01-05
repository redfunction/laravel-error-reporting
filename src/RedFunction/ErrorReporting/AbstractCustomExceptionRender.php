<?php

namespace RedFunction\ErrorReporting;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\View\Engines\PhpEngine;
use RedFunction\ErrorReporting\Interfaces\IOptionReport;
use RedFunction\ErrorReporting\Interfaces\IReportException;
use RedFunction\ErrorReporting\Traits\DoNotReportToEmail;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class ExceptionReportHandler
 *
 * @package RedFunction\ErrorReporting
 */
abstract class AbstractCustomExceptionRender
{
    const LOG_INFO = 1;
    const LOG_WARNING = 2;
    const LOG_NOTICE = 3;
    const LOG_ERROR = 3;

    /**
     * Log type
     *
     * @var string
     */
    private $logType = 0;

    /**
     * Log message
     *
     * @var string
     */
    private $logMessage = "";

    protected function log($type, $message)
    {
        $this->logType = $type;
        $this->logMessage = $message;
    }

    /**
     * 1 - INFO
     * 2 - WARNING
     * 3 - NOTICE
     * 4 - ERROR
     * @return integer
     */
    public function getLogType()
    {
        return $this->logType;
    }

    /**
     * @return string
     */
    public function getLogMessage()
    {
        return $this->logMessage;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public abstract function render($request, $e);

    /**
     * AbstractCustomExceptionRender constructor.
     */
    public abstract function __construct();

}