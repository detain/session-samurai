# Session Samurai 🥷

[![Build Status](https://travis-ci.com/detain/session-samurai.png?branch=master)](https://travis-ci.com/detain/session-samurai)

Universal high-speed asynchronous (non-blocking) SessionHandlerInterface implementation for PHP supporting shared memory, redis, memcached, mysqli, pdo, mongodb, and local file storage.
 
"_Session handling is like a sword fight_<br>
_You must think first before you move_<br>
_When it's properly used it's almost invincible_"


## Installation

Use [composer](http://getcomposer.org/) to include the save handler in your application.
```bash
composer requre detain/session-samurai
```

## Usage

```php
require 'vendor/autoload.php';  // set up autoloading using composer

$memcached = new Memcached();  // create connection to memcached
$memcached->addServer('localhost', 11211);

$handler = new Detain\SessionSamurai\Memcached($memcached);  // register handler (PHP 5.3 compatible)

session_set_save_handler(
    array($handler, 'open'),    
    array($handler, 'close'),
    array($handler, 'read'),
    array($handler, 'write'),
    array($handler, 'destroy'),
    array($handler, 'gc')
);

register_shutdown_function('session_write_close');  // the following prevents unexpected effects when using objects as save handlers

session_start();

$_SESSION['serialisation'] = 'should be in json';  // start using the session
```

## Development notes


### Related sites with possibly good reference material

* [PHP: SessionHandlerInterface](https://www.php.net/manual/en/class.sessionhandlerinterface.php) - Manual
* [PHP: session_set_save_handler](https://www.php.net/manual/en/function.session-set-save-handler.php) - Manual
* [PHP: Securing Session INI Settings](https://www.php.net/manual/en/session.security.ini.php) - Manual
* [Session Handler Life Cycle](https://gist.github.com/franksacco/d6e943c41189f8ee306c182bf8f07654): A complete overview of the php session handler life cycle]
* [1ma/RedisSessionHandler](https://github.com/1ma/RedisSessionHandler): An alternative Redis session handler for PHP featuring per-session locking and session fixation protection
* [cballou/MongoSession](https://github.com/cballou/MongoSession): A PHP session handler wrapped around MongoDB.
* [josantonius/php-session](https://github.com/josantonius/php-session): PHP library for handling sessions
* [psr7-sessions/storageless](https://github.com/psr7-sessions/storageless): storage-less PSR-7 session support
* [ramazancetinkaya/session-handler](https://github.com/ramazancetinkaya/session-handler): A PHP library for secure session handling.
* [davidlienhard/sessionhandler](https://github.com/davidlienhard/sessionhandler): 🐘 php sessionhandler using database connection
* [zahycz/sessionless](https://github.com/zahycz/sessionless): Non-I/O blocking SessionHandler implementation using Nette/Caching
* [detain/session-samurai](https://github.com/detain/session-samurai): A custom PHP session save handler for storing sessions as JSON in memcached.
* [javis/php-memcached-sessions](https://github.com/javis/php-memcached-sessions): A PHP session handler that uses memcached to store session with multiple servers, failover and replication support.
* [PHP Cache](https://www.php-cache.com/en/latest/) - PHP-Cache Documentation
* [The Cache Component](https://symfony.com/doc/current/components/cache.html#available-cache-adapters) (Symfony Docs)
* [The Lock Component](https://symfony.com/doc/current/components/lock.html#available-stores) (Symfony Docs)
