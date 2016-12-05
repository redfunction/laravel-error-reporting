<?php

use Illuminate\Foundation\Testing\TestCase;

class ReportingTest extends TestCase{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {

    }

    public function setUp()
    {

    }

    public function tearDown()
    {

    }

    public function testErrorReportingFlow(){
        $this->call('GET', '/');
        dd($this);
    }
}