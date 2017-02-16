<?php

namespace SinSquare\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ApcCache;

class ApcTest extends AbstractCacheTest
{
    public function testCache()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'apc',
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
        $this->assertInstanceOf(ApcCache::class, $cache);

        $this->checkSaveAndRead($cache);
    }
}
