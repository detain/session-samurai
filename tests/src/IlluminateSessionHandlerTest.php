<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;

class IlluminateSessionHandlerTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('Illuminate\Session\SessionManager')) {
            $this->markTestSkipped('illuminate/session is not installed');
        }
    }

    public function testPlaceholder(): void
    {
        $this->assertTrue(true);
    }
}
