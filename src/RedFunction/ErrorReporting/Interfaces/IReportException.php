<?php

namespace RedFunction\ErrorReporting\Interfaces;

/**
 * Interface IRaportException
 *
 * @package RedFunction\ErrorReporting\Interfaces
 */
interface IReportException
{
    /**
     * @return string
     */
    public function getLogMessage();

    /**
     * 1 - INFO
     * 2 - WARNING
     * 3 - NOTICE
     * 4 - ERROR
     * @return integer
     */
    public function getLogType();

    /**
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|null
     */
    public function getRedirectPage();
}