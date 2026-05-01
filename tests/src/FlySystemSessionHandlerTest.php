<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\FlySystemSessionHandler;
use League\Flysystem\FilesystemOperator;

class FlySystemSessionHandlerTest extends TestCase
{
    private FilesystemOperator $filesystem;
    private FlySystemSessionHandler $handler;

    public function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->handler = new FlySystemSessionHandler($this->filesystem, '/sessions');
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('/path', 'PHPSESSID'));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testReadNonExistent()
    {
        $this->filesystem->method('read')->willThrowException(new \League\Flysystem\UnableToReadFile('/sessions/abc123'));
        $this->assertSame('', $this->handler->read('abc123'));
    }

    public function testRead()
    {
        $this->filesystem->method('read')->willReturn('sessiondata');
        $this->assertSame('sessiondata', $this->handler->read('abc123'));
    }

    public function testWrite()
    {
        $this->assertTrue($this->handler->write('abc123', 'data'));
    }

    public function testDestroyExisting()
    {
        $this->assertTrue($this->handler->destroy('abc123'));
    }

    public function testDestroyNonExistent()
    {
        $this->filesystem->method('delete')->willThrowException(new \League\Flysystem\UnableToDeleteFile('/sessions/abc123'));
        $this->assertTrue($this->handler->destroy('abc123'));
    }

    public function testGc()
    {
        $this->filesystem->method('listContents')->willReturn(new \League\Flysystem\DirectoryListing([]));
        $this->assertNotFalse($this->handler->gc(100));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $sid = $this->handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testValidateId()
    {
        $this->filesystem->method('fileExists')->willReturn(true);
        $this->assertTrue($this->handler->validateId('abc123'));
    }

    public function testUpdateTimestamp()
    {
        $this->filesystem->method('read')->willReturn('data');
        $this->assertTrue($this->handler->updateTimestamp('abc123', 'data'));
    }
}
