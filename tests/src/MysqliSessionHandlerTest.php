<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MysqliSessionHandler;

class MysqliSessionHandlerTest extends TestCase
{
    public function testSessionIdInterface()
    {
        $mockHandler = $this->getMockBuilder(MysqliSessionHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create_sid', 'validateId'])
            ->getMock();

        $mockHandler->expects($this->once())
            ->method('create_sid')
            ->willReturn('96d2dd71a38d185f9c9ad3e3a41b605e');

        $this->assertEquals(
            '96d2dd71a38d185f9c9ad3e3a41b605e',
            $mockHandler->create_sid()
        );

        $mockHandler->expects($this->once())
            ->method('validateId')
            ->with('96d2dd71a38d185f9c9ad3e3a41b605e')
            ->willReturn(true);

        $this->assertTrue($mockHandler->validateId('96d2dd71a38d185f9c9ad3e3a41b605e'));
    }

    public function testSessionHandlerInterface()
    {
        $mockHandler = $this->getMockBuilder(MysqliSessionHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['open', 'close', 'read', 'write', 'destroy', 'gc'])
            ->getMock();

        $mockHandler->expects($this->once())
            ->method('open')
            ->with('/path/to/sessions', 'session_name')
            ->willReturn(true);

        $this->assertTrue($mockHandler->open('/path/to/sessions', 'session_name'));

        $mockHandler->expects($this->once())
            ->method('close')
            ->willReturn(true);

        $this->assertTrue($mockHandler->close());

        $sessionData = 'data1;data2;data3';

        $mockHandler->expects($this->once())
            ->method('read')
            ->with('session_id')
            ->willReturn($sessionData);

        $this->assertEquals($sessionData, $mockHandler->read('session_id'));

        $mockHandler->expects($this->once())
            ->method('write')
            ->with('session_id', $sessionData)
            ->willReturn(true);

        $this->assertTrue($mockHandler->write('session_id', $sessionData));

        $mockHandler->expects($this->once())
            ->method('destroy')
            ->with('session_id')
            ->willReturn(true);

        $this->assertTrue($mockHandler->destroy('session_id'));

        $mockHandler->expects($this->once())
            ->method('gc')
            ->with(1000)
            ->willReturn(1);

        $this->assertNotFalse($mockHandler->gc(1000));
    }

    public function testSessionUpdateTimestampHandlerInterface()
    {
        $mockHandler = $this->getMockBuilder(MysqliSessionHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateTimestamp'])
            ->getMock();

        $mockHandler->expects($this->once())
            ->method('updateTimestamp')
            ->with('session_id', 'time')
            ->willReturn(true);

        $this->assertTrue($mockHandler->updateTimestamp('session_id', 'time'));
    }

    public function testCreateSidIsSecure()
    {
        $mockDb = $this->createMock(\mysqli::class);
        $handler = new MysqliSessionHandler($mockDb);
        $sid = $handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }
}
