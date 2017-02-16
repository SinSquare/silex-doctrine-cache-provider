<?php

namespace SinSquare\Tests\Functional;

use Pimple\Container;
use Doctrine\Common\Cache\CacheProvider;

abstract class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $pimple;

    protected function setUp()
    {
        $this->pimple = new Container();
    }

    protected function tearDown()
    {
        unset($this->pimple);
    }

    protected function checkSaveAndRead(CacheProvider $cache, $key = null, $value = null)
    {
        if (!$key) {
            $key = sha1(uniqid('key'));
        }
        if (!$value) {
            $value = sha1(uniqid('value'));
        }

        $saved = $cache->save($key, $value);
        $contains = $cache->contains($key);
        $read = $cache->fetch($key);

        $this->assertSame(true, $saved);
        $this->assertSame(true, $contains);
        $this->assertSame($value, $read);
    }
}
