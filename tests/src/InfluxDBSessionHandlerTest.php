<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\InfluxDbSessionHandler;

class InfluxDBSessionHandlerTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('InfluxDB\Client')) {
            $this->markTestSkipped('influxdb/influxdb-php is not installed (v1 client required)');
        }
    }

    public function testOpen()
    {
        $client = $this->createMock(\InfluxDB\Client::class);
        $handler = new InfluxDbSessionHandler($client, 'testdb');
        $this->assertTrue($handler->open('', 'test'));
    }

    public function testClose()
    {
        $client = $this->createMock(\InfluxDB\Client::class);
        $handler = new InfluxDbSessionHandler($client, 'testdb');
        $this->assertTrue($handler->close());
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $client = $this->createMock(\InfluxDB\Client::class);
        $handler = new InfluxDbSessionHandler($client, 'testdb');
        $sid = $handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $sid);
    }

    public function testValidateId()
    {
        $client = $this->createMock(\InfluxDB\Client::class);
        $handler = new InfluxDbSessionHandler($client, 'testdb');
        $sid = $handler->create_sid();
        $this->assertTrue($handler->validateId($sid));
        $this->assertFalse($handler->validateId('invalid-session-id'));
    }
}
