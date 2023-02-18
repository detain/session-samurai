<?php

namespace Detain\SessionSamuraiTest;

 use PHPUnit\Framework\TestCase;

class PdoSessionHandlerTest extends TestCase
{
    /**
     * @var PdoSessionHandler
     */
    private $pdoSessionHandler;

    /**
     * @before
     */
    public function setupTestClass(){
        $this->pdoSessionHandler = new PdoSessionHandler();
    }

    public function testOpen(){
        $this->assertTrue($this->pdoSessionHandler->open());
    }

    public function testClose(){
        $this->assertTrue($this->pdoSessionHandler->close());
    }

    public function testRead(){
        $data = 'my test data';
        $key =  uniqid();
        $this->pdoSessionHandler->write($key, $data);

        $resultData = $this->pdoSessionHandler->read($key);
        $this->assertEquals($data, $resultData);
    }

    public function testWrite(){
        $data = 'my test data';
        $key =  uniqid();
        $this->assertTrue($this->pdoSessionHandler->write($key, $data));
    }

    public function testDestroy(){
        $data = 'my test data';
        $key =  uniqid();
        $this->pdoSessionHandler->write($key, $data);

        $this->assertTrue($this->pdoSessionHandler->destroy($key));
    }

    public function testGarbageCollection(){
        $data = 'my test data';
        $key =  uniqid();
        $this->pdoSessionHandler->write($key, $data);

        $this->assertTrue($this->pdoSessionHandler->gc(0));
    }

    public function testUpdateTimestamp(){
        $data = 'my test data';
        $key =  uniqid();
        $this->pdoSessionHandler->write($key, $data);

        $this->assertTrue($this->pdoSessionHandler->updateTimestamp($key, '1234567890'));
    }
}
