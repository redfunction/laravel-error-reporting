<?php

namespace RedFunction\ErrorReporting\Interfaces;

/**
 * Interface IOptionReport
 *
 * @package RedFunction\ErrorReporting\Interfaces
 */
interface IOptionReport
{
    /**
     * @return bool
     */
    public function canReportToEmail();
}