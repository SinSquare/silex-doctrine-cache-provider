<?php

namespace Sins\Tests;

use Sins\Cache\DoctrineCacheServiceProvider;
use Pimple\Container;
use Doctrine\Common\Cache\VoidCache;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSingleCache()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                )
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());
        $this->assertInstanceOf(VoidCache::class, $pimple["doctrine.caches"]['cache_1']);
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
                )
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $this->assertInstanceOf(VoidCache::class, $pimple['default_cache']);
        $this->assertInstanceOf(VoidCache::class, $pimple["doctrine.caches"]['cache_1']);
    }

    public function testCacheNoStore()
    {
        $pimple = new Container();
        $pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_1' => array(
                    'type' => 'void',
                )
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $o1 = $pimple["doctrine.caches"]['cache_1'];
        $o2 = $pimple["doctrine.caches"]['cache_1'];

        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertInstanceOf(VoidCache::class, $o1);
        $this->assertSame($o1, $o2);
    }





    public function testInstance()
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
                'cache_2' => array(
                    'type' => 'void',
                ),
            ),
        );
        $pimple->register(new DoctrineCacheServiceProvider());

        $this->assertInstanceOf(VoidCache::class, $pimple['default_cache']);
        $this->assertInstanceOf(VoidCache::class, $pimple["doctrine.caches"]['cache_1']);
    }








}
