<?php

namespace RedFunction\ErrorReporting\Examples;

use Exception;
use RedFunction\ErrorReporting\Interfaces\IReportException;
use RedFunction\ErrorReporting\Traits\DoNotReportToEmail;


/**
 * Class ExceptionNotUsingReport
 */
class ExceptionNotUsingReport extends Exception implements IReportException
{
    use DoNotReportToEmail;

    /**
     * @inheritdoc
     */
    public function getLogMessage(): string
    {
        return $this->getMessage();
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