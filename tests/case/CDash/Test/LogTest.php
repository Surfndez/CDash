<?php
use CDash\Test\Log;

class LogTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $log = new Log();
        $this->assertInstanceOf(\CDash\Log::class, $log);
        Log::setInstance(\CDash\Log::class, $log);
    }

    public function tearDown()
    {
        Log::getInstance()->clear();
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    public function testWrite()
    {
        $e = new Exception('This is my Error. There are many like it, but this one is mine');

        /** @var Log $log */
        $log = Log::getInstance();

        $this->assertEmpty($log->getLogEntries());

        $log->error($e);

        $entries = $log->getLogEntries();

        $expected = 'This is my Error. There are many like it, but this one is mine';
        $actual = $entries[0]['message'];
        $this->assertEquals($expected, $actual);

        $expected = LOG_ERR;
        $actual = $entries[0]['level'];
        $this->assertEquals($expected, $actual);
    }

    public function testClear()
    {
        $e = new Exception('This is my Error. There are many like it, but this one is mine');

        /** @var Log $log */
        $log = Log::getInstance();

        $this->assertEmpty($log->getLogEntries());

        $log->error($e);
        $this->assertCount(1, $log->getLogEntries());

        $log->clear();
        $this->assertEmpty($log->getLogEntries());
    }
}