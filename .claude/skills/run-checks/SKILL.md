---
name: run-checks
description: Runs the full composer test suite (phpcs + phpstan + phpunit) and interprets failures for the session-samurai project. Use when the user says 'run tests', 'check code', 'does it pass', 'verify my changes', or before committing. Knows phpcs uses phpcs.xml (PSR-12 with exclusions), phpstan uses phpstan.neon (level: max, src/ only), and phpunit bootstraps via tests/Bootstrap.php with optional TestConfiguration.php. Do NOT use mid-refactor when tests are intentionally broken.
---
# run-checks

## Critical

- **Never skip a failing tool** — `composer test` runs phpcs → phpstan → phpunit in sequence and stops on first tool failure. If phpcs fails, phpstan and phpunit do not run. Fix each tool's failures before moving on.
- **Do not run mid-refactor** — only run when the handler implementation is complete and all interfaces are fully implemented.
- **TestConfiguration.php must exist** — `tests/Bootstrap.php` loads `tests/TestConfiguration.php` if present, otherwise falls back to `tests/TestConfiguration.php.dist`. Tests that hit real backends (Redis, Memcached, MySQL) require live services at the configured hosts.
- **phpstan runs at level: max** — every type annotation error is fatal. The config is `phpstan.neon` at repo root, scanning `src/` only.

## Instructions

1. **Run the full suite**
   ```bash
   composer test
   ```
   This executes in order: `./vendor/bin/phpcs`, `./vendor/bin/phpstan analyse`, `./vendor/bin/phpunit --bootstrap tests/Bootstrap.php tests/src/`.
   Verify: all three tools exit 0 before treating the branch as clean.

2. **If phpcs fails — fix PSR-12 violations**
   phpcs checks `src/` and `tests/` against `phpcs.xml` (PSR12 base with four exclusions).
   Active exclusions — these rules are **not enforced**:
   - `Squiz.Functions.MultiLineFunctionDeclaration.BraceSpacing`
   - `PSR2.Methods.FunctionClosingBrace.SpacingBeforeClose`
   - `Generic.Files.LineLength.TooLong` (line length is ignored — do not wrap long lines to fix phpcs)
   - `Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine`

   To check only changed files:
   ```bash
   composer phpcs-diff
   ```
   (Equivalent to `git diff --name-only origin/master | xargs ls -d 2>/dev/null | xargs ./vendor/bin/phpcs`)

   Verify: `composer phpcs` exits 0 before proceeding to Step 3.

3. **If phpstan fails — fix type errors**
   phpstan scans `src/` only at level max with these relaxations in `phpstan.neon`:
   - `treatPhpDocTypesAsCertain: false`
   - `checkMissingIterableValueType: false`

   Common causes in this project:
   - `gc()` must declare return type `int|false` (PHP 8.0+)
   - `read()` must declare return type `string` (never `string|false`)
   - Constructor parameter types must match the exact extension class (e.g., `\Redis`, `\Memcached`, `\SQLite3`)

   Run standalone:
   ```bash
   composer phpstan
   ```
   Verify: exits 0 before proceeding to Step 4.

4. **If phpunit fails — diagnose the failure type**
   phpunit is bootstrapped by `tests/Bootstrap.php`, which:
   - Autoloads `vendor/autoload.php`
   - Sets `error_reporting(E_ALL | E_STRICT)`
   - Loads `tests/TestConfiguration.php` (or `.dist` fallback)
   - Adds `src/` and `tests/` to the include path

   Run standalone:
   ```bash
   composer phpunit
   ```

   See Step 5 for specific failure causes and fixes.
   Verify: exits 0 with no failures or errors.

5. **Interpreting phpunit failures** (see Common Issues below for messages)
   - **Connection refused / backend unreachable**: the test needs a live service. Check `tests/TestConfiguration.php.dist` for the expected host/port constants (e.g., `TESTS_MEMCACHE_HOST`, `TESTS_MEMCACHE_PORT`).
   - **`read()` returns false instead of ''`**: the handler's `read()` must return `''` on miss, not `false` or `null`.
   - **`gc()` type mismatch**: must return `int|false`, not `bool` or `void`.
   - **`validateId()` returns wrong bool**: should return `true` if the key exists in the backend, `false` otherwise — not always `true`.

## Examples

**User says:** "Do my changes pass?"

**Actions taken:**
1. Run `composer test` from the repo root.
2. phpcs reports a violation in `src/NewBackendSessionHandler.php` line 42: `Expected 1 blank line after function; 0 found`.
3. Add the blank line. Re-run `composer phpcs` — exits 0.
4. phpstan reports: `Method read() should return string but returns string|false.` Fix: change `return $result ?: '';` to `return $result === false ? '' : $result;`. Re-run `composer phpstan` — exits 0.
5. phpunit: 1 failure — `testRead` asserts `''` but got `false`. The fix above already resolves this. Re-run `composer phpunit` — exits 0.

**Result:** All three tools exit 0. Branch is clean.

---

**User says:** "Run tests before I commit"

**Actions taken:**
1. `composer test` — all pass.
2. Report: "phpcs, phpstan, and phpunit all pass. Safe to commit."

## Common Issues

**`PHP Fatal error: Cannot redeclare ...` during phpunit bootstrap**
- Cause: `tests/TestConfiguration.php` and `.dist` both define the same constant without guard.
- Fix: All constants in `TestConfiguration.php` must use `defined('X') || define('X', ...)` pattern — never bare `define()`.

**`Connection refused` or `Redis connection failed` in RedisSessionHandlerTest**
- Cause: phpunit test connects to a hardcoded Redis host. Check `tests/src/RedisSessionHandlerTest.php` — `setUpBeforeClass()` calls `$redis->connect(host, port)`.
- Fix: Ensure the Redis service is running at the configured host/port, or skip the test with `$this->markTestSkipped('Redis not available')`.

**`Call to undefined method` on phpstan level max**
- Cause: phpstan level max enforces that method calls on mixed/untyped variables are resolved. Add `@var ClassName $var` phpdoc or explicit cast before the call.

**phpcs error: `A closing tag is not permitted at the end of a PHP file`**
- Fix: Remove the closing `?>` tag from the end of any `src/` or `tests/` file.

**`composer test` stops after phpcs with exit code 1 but no visible error**
- Cause: phpcs found warnings-as-errors or a file in `tests/` that's excluded from your mental model but included per `phpcs.xml` (`<file>tests</file>`).
- Fix: Run `./vendor/bin/phpcs --report=full` for the complete list with file paths and line numbers.

**`Allowed memory size exhausted` during phpstan**
- Fix: `php -d memory_limit=512M ./vendor/bin/phpstan analyse`