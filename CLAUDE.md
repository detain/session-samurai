# Session Samurai

PHP library (`detain/session-samurai`) providing universal `SessionHandlerInterface` implementations. Namespace: `Detain\SessionSamurai\` → `src/`. Tests: `Detain\SessionSamuraiTest\` → `tests/src/`.

## Commands

```bash
composer test          # phpcs + phpstan + phpunit (all three)
composer phpcs         # ./vendor/bin/phpcs (PSR-12 via phpcs.xml)
composer phpstan       # ./vendor/bin/phpstan analyse (config: phpstan.neon)
composer phpunit       # ./vendor/bin/phpunit --bootstrap tests/Bootstrap.php tests/src/
composer phpcs-diff    # phpcs on changed files only
```

## Handler Pattern

All handlers in `src/` implement three interfaces:

```php
namespace Detain\SessionSamurai;

class MyBackendSessionHandler implements
    \SessionHandlerInterface,
    \SessionIdInterface,
    \SessionUpdateTimestampHandlerInterface
{
    public function open(string $savePath, string $sessionName): bool { return true; }
    public function close(): bool { return true; }
    public function read(string $sessionId): string { /* fetch from backend */ }
    public function write(string $sessionId, string $data): bool { /* upsert to backend */ }
    public function destroy(string $sessionId): bool { /* delete from backend */ }
    public function gc(int $maxLifetime): int|false { return 0; }
    public function create_sid(): string { return bin2hex(random_bytes(32)); }
    public function validateId(string $sessionId): bool { /* check existence */ }
    public function updateTimestamp(string $sessionId, string $data): bool { /* touch TTL */ }
}
```

- `create_sid()` must use cryptographically secure entropy — prefer `bin2hex(random_bytes(32))`
- `read()` returns `''` (empty string) when session not found — never `false` or `null`
- `gc()` returns `int|false` on PHP 8.0+ (number of deleted records, or `false` on error)
- Accept dependencies via constructor (e.g., `Redis &$redis`, `\Memcached $memcached`, `Connection $connection`)
- Use `$keyPrefix` or `$prefix` property to namespace keys and avoid collisions

## Existing Handlers (`src/`)

- **In-memory / cache**: `APCuSessionHandler`, `ApcSessionHandler`, `OpCacheSessionHandler`, `WinCacheSessionHandler`
- **Key-value stores**: `RedisSessionHandler` (phpredis, key prefix `PHPREDIS_SESSION:`), `MemcachedSessionHandler` (prefix `sess-`)
- **Databases**: `MysqliSessionHandler`, `PDOSessionHandler`, `SQLite3SessionHandler`, `DoctrineDBALSessionHandler`, `DoctrineSessionHandler`
- **Document / time-series**: `MongoDbSessionHandler`, `InfluxDbSessionHandler`
- **Cache abstractions**: `SymfonyCacheSessionHandler` (Symfony `AdapterInterface`), `PhpFastCacheSessionHandler` (`CacheManager`), `PhpCacheSessionHandler`
- **Filesystem**: `FileSessionHandler`, `FlySystemSessionHandler` (`FilesystemInterface`)
- **IPC**: `SemaphoreSessionHandler` (POSIX `sem_get`/`shm_attach`)
- **Framework**: `IlluminateSessionHandler` (Laravel `SessionManager`)

## Test Pattern

Tests live in `tests/src/`, bootstrapped via `tests/Bootstrap.php` (sets include path, loads `vendor/autoload.php`, reads `tests/TestConfiguration.php` or `tests/TestConfiguration.php.dist`).

```php
namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MyBackendSessionHandler;

class MyBackendSessionHandlerTest extends TestCase
{
    protected $handler;

    public function setUp(): void
    {
        // instantiate handler with real or mock dependency
        $this->handler = new MyBackendSessionHandler(/* ... */);
    }

    public function testOpen(): void { $this->assertTrue($this->handler->open('', '')); }
    public function testClose(): void { $this->assertTrue($this->handler->close()); }
    public function testReadEmpty(): void { $this->assertSame('', $this->handler->read('nonexistent')); }
    public function testWriteRead(): void
    {
        $id = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($id, 'data'));
        $this->assertSame('data', $this->handler->read($id));
    }
    public function testDestroy(): void
    {
        $id = $this->handler->create_sid();
        $this->handler->write($id, 'data');
        $this->assertTrue($this->handler->destroy($id));
        $this->assertSame('', $this->handler->read($id));
    }
    public function testGc(): void { $this->assertNotFalse($this->handler->gc(3600)); }
    public function testCreateSid(): void { $this->assertIsString($this->handler->create_sid()); }
    public function testValidateId(): void
    {
        $id = $this->handler->create_sid();
        $this->handler->write($id, 'data');
        $this->assertTrue($this->handler->validateId($id));
    }
    public function testUpdateTimestamp(): void
    {
        $id = $this->handler->create_sid();
        $this->handler->write($id, 'data');
        $this->assertTrue($this->handler->updateTimestamp($id, 'data'));
    }
}
```

## Code Quality

- **Style**: PSR-12 enforced by `phpcs.xml` (excludes `LineLength.TooLong`, `BraceSpacing`, `MultiLineFunctionDeclaration`)
- **Static analysis**: `phpstan.neon` — fix all errors before committing
- Run `composer test` before every commit to catch style and type issues early
- Never use `md5(uniqid())` for `create_sid()` — use `bin2hex(random_bytes(32))` for security
- Validate session IDs with `preg_match('/^[0-9a-f]{64}$/', $sessionId)` (for 32-byte hex IDs)

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md .agents/ .opencode/ 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->
