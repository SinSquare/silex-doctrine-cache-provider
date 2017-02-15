<?php

namespace Sins\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\PhpFileCache;

class PhpFileTest extends AbstractFileCacheTest
{
    public static function setUpBeforeClass()
    {
        self::$CACHE_DIR = self::$BASE_CACHE_DIR.'phpfile/';
    }

    public function testPhpfileConfigErr1()
    {
        $this->expectException(\RuntimeException::class);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'phpfile',
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
    }

    public function testPhpfileConfigErr2()
    {
        chmod(self::$CACHE_DIR, 0111);

        $this->expectException(\InvalidArgumentException::class);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'phpfile',
                    'directory' => self::$CACHE_DIR,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];
    }

    public function testPhpfileConfigDirectory()
    {
        $dir = self::$CACHE_DIR;
        $real = realpath($dir);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'phpfile',
                    'directory' => $dir,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];

        $newReal = $cache->getDirectory();

        $this->assertSame($real, $newReal);
    }

    public function testPhpfileConfigExtension()
    {
        $ext = 'customextension';

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'phpfile',
                    'directory' => self::$CACHE_DIR,
                    'extension' => $ext,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];

        $newExt = $cache->getExtension();

        $this->assertSame($ext, $newExt);
    }

    public function testPhpfileDirExists()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache' => array(
                    'type' => 'phpfile',
                    'directory' => self::$CACHE_DIR,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache'];

        $this->assertInstanceOf(PhpFileCache::class, $cache);

        $this->checkSaveAndRead($cache);
    }
}
