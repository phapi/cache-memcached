<?php

namespace Phapi\Tests\Cache;

use Phapi\Cache\Memcached\Memcached;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Phapi\Cache\Memcached
 */
class MemcachedTest extends TestCase
{

    protected $cache;

    protected $key;

    protected $value;

    protected $replace;

    public function setUp()
    {
        $this->cache = new Memcached([['host' => 'localhost', 'port' => 11211]]);
        $this->key = 'test_'. time();
        $this->value = 'some test value';
        $this->replace = 'replaced test value';
    }

    public function testConstructor()
    {
        $cache = new Memcached([['host' => 'localhost', 'port' => 11211]]);
        $this->assertTrue($cache->flush());
    }

    public function testNotConnected()
    {
        $this->setExpectedException('Exception', 'Unable to connect to Memcache(d) backend');
        $cache = new Memcached([['host' => 'localhost', 'port' => 1111]]);
    }

    public function testPool()
    {
        $cache = new Memcached([
            ['host' => 'localhost', 'port' => 11211],
            ['host' => 'localhost', 'port' => 11211]
        ]);
        $this->assertTrue($cache->flush());
    }

    public function testSetGet()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));
        // get the value based on the key and validate it
        $this->assertEquals($this->value, $this->cache->get($this->key));
        $this->assertTrue($this->cache->set($this->key, $this->replace));
        $this->assertEquals($this->replace, $this->cache->get($this->key));
    }

    public function testHas()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));
        // check if the key exists
        $this->assertTrue($this->cache->has($this->key));
    }

    public function testClear()
    {
        // set a key and a value
        $this->assertTrue($this->cache->set($this->key, $this->value));
        // remove key from cache
        $this->assertTrue($this->cache->clear($this->key));
        // check if the key exists
        $this->assertEmpty($this->cache->get($this->key));
    }
}