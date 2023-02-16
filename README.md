# Session Samurai 🥷
Universal high-speed asynchronous (non-blocking) SessionHandlerInterface implementation for PHP supporting shared memory, redis, memcached, mysqli, pdo, mongodb, and local file storage.
 
"_Session handling is like a sword fight_<br>
_You must think first before you move_<br>
_When it's properly used it's almost invincible_"

## Development notes

### Session Lifecycle Handler Calls

#### Open, write and close
session_start();
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::create_sid()
                      # SessionHandler::read('f57cvufkbu6qgfiqkksuagl257')
$_SESSION['foo'] = 'bar';
session_write_close();
                      # SessionHandler::write('f57cvufkbu6qgfiqkksuagl257', 'foo|s:3:"bar";')
                      # SessionHandler::close()

#### Resume, read and close
session_start();
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('f57cvufkbu6qgfiqkksuagl257')
echo $_SESSION['foo'];
    bar
session_write_close();
                      # SessionHandler::write('f57cvufkbu6qgfiqkksuagl257', 'foo|s:3:"bar";')
                      # SessionHandler::close()

#### Open, regenerate and write
session_start();
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('f57cvufkbu6qgfiqkksuagl257')
session_regenerate_id();
                      # SessionHandler::create_sid()
echo $_SESSION['foo'];
    bar
session_write_close();
                      # SessionHandler::write('dp1srap0fn9isne4na6mm83mt4', 'foo|s:3:"bar";')
                      # SessionHandler::close()

#### Session reset
session_start()
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('ui4odihluc5nkc1oh4gftlgtd7')
session_reset()
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('ui4odihluc5nkc1oh4gftlgtd7')
session_write_close();
                      # SessionHandler::write('ui4odihluc5nkc1oh4gftlgtd7', 'foo|s:3:"bar";')
                      # SessionHandler::close()

#### Custom session ID
session_id(sha1('my custom id'));
session_start();
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('b9361e543a36b9318334f618c3645ae270f773b6')
session_write_close();
                      # SessionHandler::write('b9361e543a36b9318334f618c3645ae270f773b6', 'a:0:{}')
                      # SessionHandler::close()
### Destruction
session_start();
                      # SessionHandler::open('C:\\server\\temp', 'PHPSESSID')
                      # SessionHandler::read('dp1srap0fn9isne4na6mm83mt4')
session_destroy();
                      # SessionHandler::destroy('dp1srap0fn9isne4na6mm83mt4')
                      # SessionHandler::close()

### Related sites with possibly good reference material

* [PHP: SessionHandlerInterface - Manual](https://www.php.net/manual/en/class.sessionhandlerinterface.php)
* [1ma/RedisSessionHandler](https://github.com/1ma/RedisSessionHandler): An alternative Redis session handler for PHP featuring per-session locking and session fixation protection
* [cballou/MongoSession](https://github.com/cballou/MongoSession): A PHP session handler wrapped around MongoDB.
* [josantonius/php-session](https://github.com/josantonius/php-session): PHP library for handling sessions
* [psr7-sessions/storageless](https://github.com/psr7-sessions/storageless): storage-less PSR-7 session support
* [compwright/php-session](https://github.com/compwright/php-session): Standalone session implementation that does not rely on the PHP session module or the $_SESSION global, ideal for Swoole or ReactPHP applications
* [ramazancetinkaya/session-handler](https://github.com/ramazancetinkaya/session-handler): A PHP library for secure session handling.
* [davidlienhard/sessionhandler](https://github.com/davidlienhard/sessionhandler): 🐘 php sessionhandler using database connection
* [zahycz/sessionless](https://github.com/zahycz/sessionless): Non-I/O blocking SessionHandler implementation using Nette/Caching
