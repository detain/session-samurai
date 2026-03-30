---
name: session-handler-test
description: Creates a PHPUnit test class in tests/src/ for a SessionHandlerInterface implementation. Use when user says 'add tests', 'write tests for handler', or when a new handler is created without tests. Generates testOpen, testClose, testRead, testWrite, testDestroy, testGc, testCreateSid, testValidateId, testUpdateTimestamp following MySessionHandlerTest.php patterns. Do NOT use for non-handler test classes or general PHPUnit scaffolding.
---
# session-handler-test

## Critical

- File MUST go in `tests/src/{ClassName}Test.php` — never elsewhere.
- Namespace MUST be `Detain\SessionSamuraiTest` (no exceptions).
- All three interfaces MUST be asserted in `testImplementsInterfaces()`: `\SessionHandlerInterface`, `\SessionIdInterface`, `\SessionUpdateTimestampHandlerInterface`.
- `testRead` MUST assert `''` (empty string) for a non-existent session ID — never `false` or `null`.
- `gc()` on PHP 8+ returns `int|false` — assert with `assertTrue` (truthy int or false) unless the handler guarantees `0` (then `assertEquals(0, ...)`).
- Run `composer phpcs` after writing — PSR-12 violations will fail CI.

## Instructions

### Step 1 — Read the handler under test

Read `src/{ClassName}.php` and identify:
- Constructor signature (dependencies: `Redis`, `\Memcached`, `Connection`, etc.)
- Whether it needs an external service (Redis, Memcached, DB) or is self-contained
- The key prefix used (e.g., `PHPREDIS_SESSION:`, `sess-`) — needed for direct backend assertions

Verify the file exists before proceeding.

### Step 2 — Choose the setup pattern

**Self-contained handler** (no external dependency — e.g., `FileSessionHandler`, `APCuSessionHandler`):
```php
protected $handler;

protected function setUp(): void
{
    $this->handler = new MyBackendSessionHandler();
    $this->handler->open(sys_get_temp_dir(), 'test');
}

protected function tearDown(): void
{
    $this->handler->close();
}
```

**External-dependency handler** (Redis, Memcached, DB — e.g., `RedisSessionHandler`):
```php
protected static $client;    // e.g. \Redis, \Memcached
protected static $sessionId;

public static function setUpBeforeClass(): void
{
    self::$client = new \Redis();
    self::$client->connect('127.0.0.1', 6379);
    self::$sessionId = bin2hex(random_bytes(32));
}
```
For external handlers, instantiate a fresh `$handler` inside each test method rather than in `setUp`.

Verify the dependency class exists (`\Redis`, `\Memcached`, etc.) before proceeding.

### Step 3 — Write the test class

Create `tests/src/{ClassName}Test.php` using this skeleton, filling in the handler-specific parts:

```php
<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\{ClassName};

class {ClassName}Test extends TestCase
{
    // Step 2 setup here

    public function testImplementsInterfaces()
    {
        // For self-contained:
        $this->assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionIdInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
        // For external: instantiate handler with self::$client first
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('', ''));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testRead()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertEquals('', $this->handler->read($sessionId));   // non-existent → ''
        $this->handler->write($sessionId, 'test');
        $this->assertEquals('test', $this->handler->read($sessionId));
    }

    public function testWrite()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertEquals('test', $this->handler->read($sessionId));
    }

    public function testDestroy()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertTrue($this->handler->destroy($sessionId));
        $this->assertSame('', $this->handler->read($sessionId));
    }

    public function testGc()
    {
        // Handlers that manage TTL internally (e.g. Redis): just assert truthy/0
        $this->assertTrue($this->handler->gc(0) !== false);
        // Handlers with real expiry logic: write two sessions, sleep(2),
        // updateTimestamp one, gc(0), assert one valid/one invalid (see MySessionHandlerTest)
    }

    public function testCreateSid()
    {
        $sid = $this->handler->create_sid();
        $this->assertIsString($sid);
        $this->assertNotEquals('', $sid);
    }

    public function testValidateId()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertTrue($this->handler->validateId($sessionId));
        $this->assertFalse($this->handler->validateId('invalid-session-id-that-does-not-exist'));
    }

    public function testUpdateTimestamp()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertTrue($this->handler->updateTimestamp($sessionId, 'test'));
    }
}
```

This step uses the constructor details from Step 1 and the setup pattern from Step 2.

### Step 4 — Verify with the test suite

Run:
```bash
composer phpunit
```
If that passes, run the full suite:
```bash
composer test
```
All three checks (`phpcs`, `phpstan`, `phpunit`) must pass before the file is done.

## Examples

**User says:** "Add tests for RedisSessionHandler"

**Actions taken:**
1. Read `src/RedisSessionHandler.php` — constructor takes `Redis &$redis`, key prefix is `PHPREDIS_SESSION:`
2. External dependency → use `setUpBeforeClass` with static `$redis` and `$sessionId`
3. Create `tests/src/RedisSessionHandlerTest.php`:

```php
<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\RedisSessionHandler;

class RedisSessionHandlerTest extends TestCase
{
    protected static $redis;
    protected static $sessionId;

    public static function setUpBeforeClass(): void
    {
        self::$redis = new \Redis();
        self::$redis->connect('127.0.0.1', 6379);
        self::$sessionId = bin2hex(random_bytes(32));
    }

    public function testOpen()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->open('', ''));
    }

    public function testRead()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertEquals('', $handler->read(self::$sessionId));
    }

    public function testWrite()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->write(self::$sessionId, 'test data'));
        $this->assertEquals('test data', $handler->read(self::$sessionId));
    }

    public function testDestroy()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->destroy(self::$sessionId));
        $this->assertEquals('', $handler->read(self::$sessionId));
    }

    public function testGc()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertEquals(0, $handler->gc(0));  // Redis manages TTL; gc is a no-op
    }

    public function testCreateSid()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $sid = $handler->create_sid();
        $this->assertIsString($sid);
        $this->assertNotEquals('', $sid);
    }

    public function testValidateId()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $handler->write(self::$sessionId, 'data');
        $this->assertTrue($handler->validateId(self::$sessionId));
        $this->assertFalse($handler->validateId('nonexistent-id'));
    }

    public function testUpdateTimestamp()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $handler->write(self::$sessionId, 'data');
        $this->assertTrue($handler->updateTimestamp(self::$sessionId, 'data'));
    }
}
```

4. Run `composer test` — all green.

**Result:** `tests/src/RedisSessionHandlerTest.php` passing all 8 tests.

## Common Issues

**`Class 'Detain\SessionSamuraiTest\SessionHandlerInterface' not found`**
You forgot the leading backslash. Use `\SessionHandlerInterface::class`, not `SessionHandlerInterface::class`.

**`read()` test fails: got `false`, expected `''`**
The handler returns `false` on miss. The handler itself is wrong — fix `read()` in `src/` to `return '';` not `return false;`. The test is correct.

**`phpcs` fails with `Method name "create_sid" is not in camelCase`**
Add the suppression comment directly above the method:
```php
// phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
public function create_sid(): string
```
This is also needed in the handler source, not the test.

**`Could not connect to Redis` / `Connection refused`**
External-dependency tests require the service running. For local dev:
```bash
docker run -d -p 6379:6379 redis:alpine
```
Or mock the dependency with `getMockBuilder(\Redis::class)->disableOriginalConstructor()->getMock()`.

**`phpstan` error: `Method gc() should return int|false but returns bool`**
Change `return true;` / `return false;` in `gc()` to `return 0;` / `return false;` — `true` is not `int|false`.

**Tests pass individually but fail together (`session_start()` conflict)**
Add `@runInSeparateProcess` docblock annotation to any test that calls `session_start()`.