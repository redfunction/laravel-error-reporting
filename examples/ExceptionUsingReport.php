<?php

namespace RedFunction\ErrorReporting\Examples;

use Exception;
use Illuminate\Http\RedirectResponse;
use RedFunction\ErrorReporting\Interfaces\IReportException;


/**
 * Class ExceptionUsingReport
 */
class ExceptionUsingReport extends Exception implements IReportException
{
    /**
     * @inheritdoc
     */
    public function getLogMessage(): string
    {
        return 'Error 500: reason...';
    }

    /**
     * @inheritdoc
     */
    public function getLogType(): int
    {
        return 4;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectPage()
    {
        return null;
    }
}