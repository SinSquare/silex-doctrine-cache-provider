<?php

namespace Sins\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ApcuCache;

class ApcuTest extends AbstractCacheTest
{
    public function testCache()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'apcu',
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
        $this->assertInstanceOf(ApcuCache::class, $cache);

        $this->checkSaveAndRead($cache);
    }
}
