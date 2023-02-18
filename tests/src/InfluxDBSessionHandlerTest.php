<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;

class InfluxDBSessionHandlerTest extends TestCase
{
    public function setUp(): void
    {
        // Create session handler instance
        $this->handler = new InfluxDBSessionHandler();
    }

    // Tests for open() method
    public function testCanOpenSession()
    {
        $result = $this->handler->open('null', 'test');

        $this->assertTrue($result);
    }

    // Tests for read() method
    public function testReadSessionData()
    {
        $expectedResult = 'testData';

        // Ensure test data is written
        $this->handler->write('testid', $expectedResult);

        $actualResult = $this->handler->read('testid');

        $this->assertEquals($expectedResult, $actualResult);
    }

    // Tests for write() method
    public function testSessionDataWritten()
    {
        $data = [
            'mydata' => 'something',
            'another' => 'value'
        ];

        $result = $this->handler->write('testid', $data);
        $this->assertTrue($result);
    }

    // Tests for close() method
    public function testCanCloseSession()
    {
        $result = $this->handler->close();

        $this->assertTrue($result);
    }

    // Tests for destroy() method
    public function testCanDestroySession()
    {
        $result = $this->handler->destroy('testid');

        $this->assertTrue($result);
    }

    // Tests for gc() method
    public function testCanRunGarbageCollection()
    {
        $result = $this->handler->gc(3600);

        $this->assertTrue($result);
    }
}
