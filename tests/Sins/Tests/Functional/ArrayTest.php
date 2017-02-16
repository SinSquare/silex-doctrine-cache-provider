<?php

namespace SinSquare\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;

class ArrayTest extends AbstractCacheTest
{
    public function testCache()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'array',
                ),
                'cache2' => array(
                    'type' => 'array',
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
        $this->assertInstanceOf(ArrayCache::class, $cache);

        $this->checkSaveAndRead($cache);
    }
}
