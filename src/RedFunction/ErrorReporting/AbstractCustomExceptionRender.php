<?php

namespace RedFunction\ErrorReporting;

use Exception;

/**
 * Class ExceptionReportHandler
 *
 * @package RedFunction\ErrorReporting
 */
abstract class AbstractCustomExceptionRender
{
    /**
     *
     */
    const LOG_INFO = 1;

    /**
     *
     */
    const LOG_WARNING = 2;

    /**
     *
     */
    const LOG_NOTICE = 3;

    /**
     *
     */
    const LOG_ERROR = 4;

    /**
     * @var string
     */
    private $logType = 0;

    /**
     * @var string
     */
    private $logMessage = '';

    /**
     * @param string $type
     * @param string $message
     */
    protected function log($type, $message)
    {
        $this->logType = $type;
        $this->logMessage = $message;
    }

    /**
     * @return integer
     */
    public function getLogType(): int
    {
        return $this->logType;
    }

    /**
     * @return string
     */
    public function getLogMessage(): string
    {
        return $this->logMessage;
    }

    /**
     * Render an exception into an HTTP response
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Exception $e
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    abstract public function render($request, $e);

    /**
     * AbstractCustomExceptionRender constructor
     */
    abstract public function __construct();
}