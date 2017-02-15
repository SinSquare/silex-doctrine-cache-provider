<?php

namespace Sins\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;

class ChainTest extends AbstractCacheTest
{
    public function testCache()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'chain',
                    'caches' => array('apc', 'apcu', 'array'),
                ),
                'apc' => array(
                    'type' => 'apc',
                ),
                'apcu' => array(
                    'type' => 'apcu',
                ),
                'array' => array(
                    'type' => 'array',
                ),
            ),
        );

        $key = 'some_key';
        $value = sha1(rand());

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
        $this->assertInstanceOf(ChainCache::class, $cache);

        $apc = $this->pimple['doctrine.caches']['apc'];
        $this->assertInstanceOf(ApcCache::class, $apc);

        $apcu = $this->pimple['doctrine.caches']['apcu'];
        $this->assertInstanceOf(ApcuCache::class, $apcu);

        $array = $this->pimple['doctrine.caches']['array'];
        $this->assertInstanceOf(ArrayCache::class, $array);

        $this->checkSaveAndRead($cache, $key, $value);

        $this->assertSame($value, $apc->fetch($key));
        $this->assertSame($value, $apcu->fetch($key));
        $this->assertSame($value, $array->fetch($key));
    }
}
