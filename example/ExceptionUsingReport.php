<?php


/**
 * Class ExceptionUsingReport
 *
 */
class ExceptionUsingReport extends Exception implements \ErrorReporting\Interfaces\IReportException
{

    /**
     * @return string
     */
    public function getLogMessage()
    {
        return "Error 500: reason...";
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
        return 4;
    }

    /**
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|null
     */
    public function getRedirectPage()
    {
        return null;
    }
}