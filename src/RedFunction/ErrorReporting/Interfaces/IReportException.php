<?php

namespace RedFunction\ErrorReporting\Interfaces;

use Illuminate\Http\RedirectResponse;

/**
 * Interface IReportException
 *
 * @package RedFunction\ErrorReporting\Interfaces
 */
interface IReportException
{
    /**
     * @return string
     */
    public function getLogMessage(): string;

    /**
     * 1 - INFO
     * 2 - WARNING
     * 3 - NOTICE
     * 4 - ERROR
     *
     * @return integer
     */
    public function getLogType(): int;

    /**
     * @return \Illuminate\Routing\Redirector|RedirectResponse|null
     */
    public function getRedirectPage();
}