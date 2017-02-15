<?php

namespace Sins\Tests\Functional;

use SinSquare\Cache\DoctrineCacheServiceProvider;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class FilesystemTest extends AbstractFileCacheTest
{
    public static function setUpBeforeClass()
    {
        self::$CACHE_DIR = self::$BASE_CACHE_DIR.'filesystem/';
    }

    public function testFilesystemConfigErr1()
    {
        $this->expectException(\RuntimeException::class);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_filesystem' => array(
                    'type' => 'filesystem',
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache_filesystem'];
    }

    public function testFilesystemConfigErr2()
    {
        chmod(self::$CACHE_DIR, 0111);

        $this->expectException(\InvalidArgumentException::class);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_filesystem' => array(
                    'type' => 'filesystem',
                    'directory' => self::$CACHE_DIR,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache_filesystem'];
    }

    public function testFilesystemConfigDirectory()
    {
        $dir = self::$CACHE_DIR;
        $real = realpath($dir);

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_filesystem' => array(
                    'type' => 'filesystem',
                    'directory' => $dir,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache_filesystem'];

        $newReal = $cache->getDirectory();

        $this->assertSame($real, $newReal);
    }

    public function testFilesystemConfigExtension()
    {
        $ext = 'customextension';

        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_filesystem' => array(
                    'type' => 'filesystem',
                    'directory' => self::$CACHE_DIR,
                    'extension' => $ext,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache_filesystem'];

        $newExt = $cache->getExtension();

        $this->assertSame($ext, $newExt);
    }

    public function testFilesystemDirExists()
    {
        $this->pimple['doctrine.cache.options'] = array(
            'providers' => array(
                'cache_filesystem' => array(
                    'type' => 'filesystem',
                    'directory' => self::$CACHE_DIR,
                ),
            ),
        );

        $this->pimple->register(new DoctrineCacheServiceProvider());

        $cache = $this->pimple['doctrine.caches']['cache_filesystem'];

        $this->assertInstanceOf(FilesystemCache::class, $cache);

        $this->checkSaveAndRead($cache);
    }
}
