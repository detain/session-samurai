<?php

declare(strict_types=1);

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MysqliSessionHandler;

final class Mysqli2SessionHandlerTest extends TestCase
{
    /**
     * @dataProvider getDataForRead
     */
    public function testRead($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected1'], $handler->read($data['input1']));
        $this->assertEquals($data['expected2'], $handler->read($data['input2']));
    }

    public function getDataForRead()
    {
        return [
            [
                [
                    'input1' => '12345',
                    'expected1' => '777',
                    'input2' => 'abcde',
                    'expected2' => '888'
                ]
            ]
        ];
    }


    /**
     * @dataProvider getDataForWrite
     */
    public function testWrite($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected1'], $handler->write($data['input1'], $data['data1']));
        $this->assertEquals($data['expected2'], $handler->write($data['input2'], $data['data2']));
    }

    public function getDataForWrite()
    {
        return [
            [
                [
                    'input1' => '12345',
                    'data1' => '777',
                    'expected1' => true,
                    'input2' => 'abcde',
                    'data2' => '888',
                    'expected2' => true
                ]
            ]
        ];
    }


    /**
     * @dataProvider getDataForUpdateTimestamp
     */
    public function testUpdateTimestamp($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected1'], $handler->updateTimestamp($data['input1'], $data['data1']));
        $this->assertEquals($data['expected2'], $handler->updateTimestamp($data['input2'], $data['data2']));
    }

    public function getDataForUpdateTimestamp()
    {
        return [
            [
                [
                    'input1' => '12345',
                    'data1' => '777',
                    'expected1' => true,
                    'input2' => 'abcde',
                    'data2' => '888',
                    'expected2' => true
                ]
            ]
        ];
    }


    /**
     * @dataProvider getDataForDestroy
     */
    public function testDestroy($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected1'], $handler->destroy($data['input1']));
        $this->assertEquals($data['expected2'], $handler->destroy($data['input2']));
    }

    public function getDataForDestroy()
    {
        return [
            [
                [
                    'input1' => '12345',
                    'expected1' => true,
                    'input2' => 'abcde',
                    'expected2' => true
                ]
            ]
        ];
    }


    /**
     * @dataProvider getDataForGC
     */
    public function testGc($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected'], $handler->gc($data['input1']));
    }

    public function getDataForGC()
    {
        return [
            [
                [
                    'input1' => '12345',
                    'expected' => true
                ]
            ]
        ];
    }


    /**
     * @dataProvider getDataForCreate_sid
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid($data): void
    {
        $handler = new MySQLiSessionHandler();

        $this->assertEquals($data['expected'], $handler->create_sid());
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function getDataForCreate_sid()
    {
        return [
            [
                [
                    'expected' => "12345"
                ]
            ]
        ];
    }
}
