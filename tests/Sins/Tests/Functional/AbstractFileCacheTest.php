<?php

namespace Sins\Tests\Functional;

abstract class AbstractFileCacheTest extends AbstractCacheTest
{
    protected $pimple;

    protected static $BASE_CACHE_DIR = __DIR__.'/../../../../var/';
    protected static $CACHE_DIR = __DIR__.'/../../../../var/';

    protected function setUp()
    {
        parent::setUp();
        @mkdir(self::$CACHE_DIR, 0777, true);
        chmod(self::$CACHE_DIR, 0777);
        self::deleAllInDirectory(self::$CACHE_DIR, false);
    }

    protected static function deleAllInDirectory($dirPath, $deleteParentDir = true)
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dirPath.DIRECTORY_SEPARATOR.$object)) {
                        self::deleAllInDirectory($dirPath.DIRECTORY_SEPARATOR.$object);
                    } else {
                        unlink($dirPath.DIRECTORY_SEPARATOR.$object);
                    }
                }
            }
            reset($objects);
            if ($deleteParentDir) {
                rmdir($dirPath);
            }
        }
    }
}
