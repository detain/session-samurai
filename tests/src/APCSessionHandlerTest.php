<?php

use PHPUnit\Framework\TestCase;

class APCSessionHandlerTest extends TestCase
{
    public function testOpen(): void 
    {
		$APCSession = new APCSessionHandler();

		$this->assertTrue($APCSession->open('test_session', 'test_save_path'));
    }

    public function testRead(): void 
    {
		$APCSession = new APCSessionHandler();
		$APCSession->open('test_session', 'test_save_path');

		$this->assertEquals('value', $APCSession->read('key'));
    }

    public function testWrite(): void 
    {
		$APCSession = new APCSessionHandler();
		$APCSession->open('test_session', 'test_save_path');

		$this->assertTrue($APCSession->write('key', 'value'));
    }

    public function testDestroy(): void
    {
		$APCSession = new APCSessionHandler();
		$APCSession->open('test_session', 'test_save_path');

        $this->assertTrue($APCSession->destroy('key'));
    }

    public function testClose(): void 
    {
		$APCSession = new APCSessionHandler();
		$APCSession->open('test_session', 'test_save_path');

		$this->assertEquals(true, $APCSession->close());
    }
}
