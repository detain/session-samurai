---
name: new-session-handler
description: Creates a new SessionHandlerInterface implementation in src/. Scaffolds all 9 required methods (open, close, read, write, destroy, gc, create_sid, validateId, updateTimestamp) with correct PHP 8.0+ return types and secure create_sid(). Use when user says 'add handler', 'new backend', 'implement session handler for X', or creates a file in src/. Do NOT use for modifying existing handlers.
---
# new-session-handler

## Critical

- All handlers MUST implement all three interfaces: `\SessionHandlerInterface`, `\SessionIdInterface`, `\SessionUpdateTimestampHandlerInterface`
- `read()` MUST return `''` (empty string) when session not found — never `false`, `null`, or any other falsy value
- `create_sid()` MUST use `bin2hex(random_bytes(32))` — never `md5(uniqid(...))` or other non-CSPRNG approaches
- `gc()` return type is `int|false` on PHP 8.0+ — return `0` when the backend handles its own TTL/expiry
- Accept the backend dependency via constructor — never connect inside `open()`
- Always define a `$keyPrefix` / `$prefix` property to namespace keys — default must not be empty
- Run `composer test` (phpcs + phpstan + phpunit) before considering the task done

## Instructions

### Step 1 — Name the class and create the file

Derive the class name: `{BackendName}SessionHandler`. Create `src/{BackendName}SessionHandler.php`.

Verify no file with that name already exists in `src/` before proceeding.

### Step 2 — Scaffold the class

Use this exact skeleton (mirror of `src/RedisSessionHandler.php`, the canonical reference):

```php
<?php

namespace Detain\SessionSamurai;

use SomeBackendClass;           // add real use-statements for the backend
use RuntimeException;

/**
 * Class {BackendName}SessionHandler
 *
 * A session handler that stores PHP session data in {BackendName}.
 */
class {BackendName}SessionHandler implements
    \SessionHandlerInterface,
    \SessionIdInterface,
    \SessionUpdateTimestampHandlerInterface
{
    /** @var BackendType */
    private BackendType $backend;

    /** @var int Session TTL in seconds */
    private int $ttl;

    /** @var string Prefix for all session keys */
    private string $keyPrefix;

    public function __construct(BackendType &$backend, int $ttl = 86400, string $keyPrefix = '{BACKENDNAME}_SESSION:')
    {
        $this->backend  = &$backend;
        $this->ttl      = $ttl;
        $this->keyPrefix = $keyPrefix;
    }

    /** {@inheritdoc} */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /** {@inheritdoc} */
    public function close(): bool
    {
        return true;  // or $this->backend->close() if applicable
    }

    /** {@inheritdoc} */
    public function read(string $sessionId): string
    {
        $data = $this->backend->get($this->keyPrefix . $sessionId);
        return $data === false ? '' : $data;
    }

    /** {@inheritdoc} */
    public function write(string $sessionId, string $data): bool
    {
        $key = $this->keyPrefix . $sessionId;
        return (bool) $this->backend->setex($key, $this->ttl, $data);
    }

    /** {@inheritdoc} */
    public function destroy(string $sessionId): bool
    {
        $this->backend->del($this->keyPrefix . $sessionId);
        return true;
    }

    /** {@inheritdoc} */
    public function gc(int $maxLifetime): int|false
    {
        // Backend handles expiry automatically via TTL.
        return 0;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /** {@inheritdoc} */
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /** {@inheritdoc} */
    public function validateId(string $sessionId): bool
    {
        return $this->backend->exists($this->keyPrefix . $sessionId) === 1;
    }

    /** {@inheritdoc} */
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        $key = $this->keyPrefix . $sessionId;
        if (!$this->backend->exists($key)) {
            return false;
        }
        return (bool) $this->backend->expire($key, $this->ttl);
    }
}
```

**Notes on adapting to different backends:**
- If the backend does NOT support per-key TTL (e.g., filesystem, database), implement `gc()` to delete expired records and return the count (or `false` on error).
- If the backend passes the connection by value (e.g., `\Memcached`), drop `&` from the constructor parameter.
- For backends without a native `exists()` call, implement `validateId()` via `read()` returning non-empty.
- For `updateTimestamp()`, only refresh the TTL — do NOT rewrite session data.

Verify: `composer phpcs` reports no errors on the new file before Step 3.

### Step 3 — Create the test file

Create `tests/src/{BackendName}SessionHandlerTest.php` mirroring `tests/src/RedisSessionHandlerTest.php`:

```php
<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\{BackendName}SessionHandler;

class {BackendName}SessionHandlerTest extends TestCase
{
    protected static $backend;
    protected static string $sessionId;

    public static function setUpBeforeClass(): void
    {
        // Establish the real backend connection here
        self::$backend  = /* new BackendType(); connect */;
        self::$sessionId = bin2hex(random_bytes(32));
    }

    public function testOpen(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->open('', ''));
    }

    public function testClose(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->close());
    }

    public function testRead(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertSame('', $handler->read(self::$sessionId));
    }

    public function testWrite(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->write(self::$sessionId, 'test data'));
        $this->assertSame('test data', $handler->read(self::$sessionId));
    }

    public function testGc(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $result  = $handler->gc(0);
        $this->assertTrue($result === false || is_int($result));
    }

    public function testCreateSid(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $sid = $handler->create_sid();
        $this->assertIsString($sid);
        $this->assertNotSame('', $sid);
    }

    public function testValidateId(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->validateId(self::$sessionId));
        $this->assertFalse($handler->validateId('totally-invalid-session-id'));
    }

    public function testUpdateTimestamp(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->write(self::$sessionId, 'test data'));
        $this->assertTrue($handler->updateTimestamp(self::$sessionId, 'test data'));
    }

    public function testDestroy(): void
    {
        $handler = new {BackendName}SessionHandler(self::$backend);
        $this->assertTrue($handler->destroy(self::$sessionId));
        $this->assertSame('', $handler->read(self::$sessionId));
    }
}
```

Verify: the test file is under `tests/src/` and the class name ends in `Test`.

### Step 4 — Run the full suite

```bash
composer test
```

All three checks (phpcs, phpstan, phpunit) must pass. Fix any reported issues before marking the task complete.

## Examples

**User says:** "Add a session handler for Valkey"

**Actions taken:**
1. Create `src/ValkeySessionHandler.php` with namespace `Detain\SessionSamurai`, implementing all 3 interfaces, accepting `Redis &$redis` (Valkey uses phpredis), `$keyPrefix = 'VALKEY_SESSION:'`.
2. Implement `read()` returning `''` on miss, `write()` using `setex()`, `gc()` returning `0`, `create_sid()` using `bin2hex(random_bytes(32))`.
3. Create `tests/src/ValkeySessionHandlerTest.php` extending `TestCase` with all 9 test methods.
4. Run `composer test` — all green.

**Result:** `src/ValkeySessionHandler.php` + `tests/src/ValkeySessionHandlerTest.php`, passing phpcs/phpstan/phpunit.

## Common Issues

**`read()` returning `false` instead of `''`**
Backend `get()` returns `false` on miss. Always guard: `return $data === false ? '' : $data;`

**phpcs error: `Method name "create_sid" is not in camelCaps format`**
Add `// phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps` on the line immediately above `public function create_sid()`.

**phpstan error: `gc(): return type int|false but returning bool`**
Return an integer (`0` or a count), not `true`/`false` for success. Return `false` only on actual error.

**phpstan error: `Cannot pass non-variable by reference`**
The constructor must declare the parameter as `BackendType &$backend` and the caller must pass a variable, not a constructor call: `$r = new Redis(); $h = new MyHandler($r);`

**`testValidateId` fails: both `assertTrue` and `assertFalse` pass the same key**
Ensure `write()` (or `setUpBeforeClass`) stores data under `self::$sessionId` before `validateId` is tested. The `testWrite` must run before `testValidateId` — PHPUnit runs methods in declaration order by default.

**`composer phpunit` cannot find `TestConfiguration.php`**
Copy the dist file: `cp tests/TestConfiguration.php.dist tests/TestConfiguration.php` and edit connection constants if needed.