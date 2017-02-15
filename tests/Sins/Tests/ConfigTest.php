<?php

namespace Sins\Tests;

use Sins\Cache\DoctrineCacheServiceProvider;
use Pimple\Container;
use Doctrine\Common\Cache\VoidCache;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testSingleCache()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());
        $this->assertInstanceOf(VoidCache::class, $pimple['doctrine.caches']['cache_1']);
    }

    public function testSingleAlias()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'aliases' => array(
                'cache_1' => 'default_cache',
            ),
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $o1 = $pimple['doctrine.caches']['cache_1'];
        $o2 = $pimple['default_cache'];

        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertSame($o1, $o2);
    }

    public function testMultipleCache()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
                'cache_2' => array(
                    'type' => 'void',
                ),
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $o1 = $pimple['doctrine.caches']['cache_1'];
        $o2 = $pimple['doctrine.caches']['cache_2'];

        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertNotSame($o1, $o2);
    }

    public function testMultipleAlias()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'aliases' => array(
                'cache_1' => 'default_cache1',
                'cache_2' => 'default_cache2',
            ),
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                ),
                'cache_2' => array(
                    'type' => 'void',
                ),
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $o11 = $pimple['doctrine.caches']['cache_1'];
        $o12 = $pimple['default_cache1'];

        $o21 = $pimple['doctrine.caches']['cache_2'];
        $o22 = $pimple['default_cache2'];

        $this->assertInstanceOf(VoidCache::class, $o11);
        $this->assertInstanceOf(VoidCache::class, $o12);

        $this->assertInstanceOf(VoidCache::class, $o21);
        $this->assertInstanceOf(VoidCache::class, $o22);

        $this->assertSame($o11, $o12);
        $this->assertSame($o21, $o22);

        $this->assertNotSame($o11, $o22);
    }
}
