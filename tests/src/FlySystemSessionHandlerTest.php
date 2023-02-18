<?php

use PHPUnit\Framework\TestCase;

class FlySystemSessionHandlerTest extends TestCase
{
    public function testConstructor()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        
        $this->assertTrue($handler instanceof SessionHandlerInterface);
        $this->assertTrue($handler instanceof SessionIdInterface);
        $this->assertTrue($handler instanceof SessionUpdateTimestampHandlerInterface);
    }
    
    public function testOpen()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->open('testSessionId', '/my/path');
        
        $this->assertTrue($result);
    }
    
    public function testClose()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->close('testSessionId');
        
        $this->assertTrue($result);
    }
    
    public function testRead()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->read('testSessionId');
        
        $this->assertEquals('', $result);
    }
    
    public function testWrite()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->write('testSessionId', 'some data');
        
        $this->assertTrue($result);
    }
    
    public function testDestroy()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->destroy('testSessionId');
        
        $this->assertTrue($result);
    }
    
    public function testGc()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->gc(100);
        
        $this->assertTrue($result);
    }
    
    public function testCreate_SID()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->create_SID();
        
        // Not sure how is generation is handled
        $this->assertNotEmpty($result);
    }
    
    public function testValidateId()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->validateId('testSessionId');
        
        // Not sure how validation is handled
        $this->assertTrue($result);
    }
    
    public function testUpdateTimestamp()
    {
        $handler = new FlySystemSessionHandler('/base/path');
        $result = $handler->updateTimestamp('testSessionId', 100);
        
        $this->assertTrue($result);
    }
}
