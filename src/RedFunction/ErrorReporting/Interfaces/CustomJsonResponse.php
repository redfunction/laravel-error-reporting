<?php

namespace RedFunction\ErrorReporting\Interfaces;

/**
 * Interface CustomJsonResponse
 *
 * @package RedFunction\ErrorReporting\Interfaces
 */
interface CustomJsonResponse
{
    /**
     * @param array $data
     * @return array
     */
    public function customJsonResponse($data);
}