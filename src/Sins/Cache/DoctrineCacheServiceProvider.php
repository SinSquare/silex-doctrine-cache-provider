<?php

namespace Sins\Cache;

use Doctrine\Common\Cache\VoidCache;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['doctrine.cache.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['doctrine.cache.options'])) {
                throw new \Exception("'doctrine.cache.options' not set, can't initialize!");
            }

            $tmp = $app['doctrine.cache.options'];

            $storeInstance = false;
            if(isset($tmp["storeInstance"])) {
                if(!is_bool($tmp["storeInstance"])) {
                    throw new \Exception("'doctrine.cache.options => storeInstance' must be boolean, can't initialize!");
                }
                $storeInstance = $tmp["storeInstance"];
            }

            if (!is_array($tmp['providers'])) {
                throw new \Exception("'doctrine.cache.options => providers' not set or not array, can't initialize!");
            } else {
                foreach ($tmp['providers'] as $name => &$options) {
                    if(!is_array($options)) {
                        throw new \Exception("'doctrine.cache.options => providers => {$name}' not set or not array, can't initialize!");
                    } elseif(!isset($options["type"]) || !is_string($options["type"])) {
                        throw new \Exception("'doctrine.cache.options => providers => {$name} => type' not set or not string, can't initialize!");
                    } elseif(isset($options["storeInstance"]) && !is_string($options["storeInstance"])) {
                        throw new \Exception("'doctrine.cache.options => providers => {$name} => storeInstance' set but not bool, can't initialize!");
                    }

                    if(!isset($options["storeInstance"])) {
                        $options["storeInstance"] = $storeInstance;
                    }
                }
            }

            if (!isset($tmp['aliases'])) {
                $tmp['aliases'] = array();
            } elseif(!is_array($tmp['aliases'])) {
                throw new \Exception("'doctrine.cache.options => aliases' not array, can't initialize!");
            } else {
                foreach ($tmp['aliases'] as $name => $alias) {
                    if(!is_string($name)) {
                        throw new \Exception("'doctrine.cache.options => aliases' contains not string name, can't initialize!");
                    } elseif(!is_string($alias)) {
                        throw new \Exception("'doctrine.cache.options => aliases' contains not string alias, can't initialize!");
                    } elseif(!isset($tmp['providers'][$name])) {
                        throw new \Exception("'doctrine.cache.options => aliases => {$name}' no provider found, can't initialize!");
                    }
                }
            }

            $app['doctrine.cache.options'] = $tmp;
        });

        /*
        CACHE START
        */

        $app['doctrine.cache.locator'] = $app->protect(function ($cacheName, $options) use ($app) {

            if (!isset($options['type'])) {
                throw new \RuntimeException("No type specified for '{$cacheName}'");
            }


            $store = false;
            if(isset($options["storeInstance"]) && $options["storeInstance"] === true) {
                $store = $options["storeInstance"];
            }

            var_dump($store);

            $cacheKey = sprintf("doctrine.cache.store.%s", $cacheName);



            if($store == true && isset($app[$cacheKey])) {
                var_dump("#1");
                return $app[$cacheKey];
            }

            $cache = $app['doctrine.cache.factory']($options);

            if (isset($options['namespace']) && $cache instanceof CacheProvider) {
                $cache->setNamespace($options['namespace']);
            }

            if($store) {
                var_dump("#2");
                $app[$cacheKey] = $cache;
            }

            return $cache;
        });

        $app['doctrine.cache.factory.backing_memcache'] = $app->protect(function () {
            return new \Memcache();
        });

        $app['doctrine.cache.factory.memcache'] = $app->protect(function ($cacheOptions) use ($app) {
            throw new \RuntimeException('memcache not implemented');
            if (empty($cacheOptions['host']) || empty($cacheOptions['port'])) {
                throw new \RuntimeException('Host and port options need to be specified for memcache cache');
            }

            /** @var \Memcache $memcache */
            $memcache = $app['doctrine.orm.cache.factory.backing_memcache']();
            $memcache->connect($cacheOptions['host'], $cacheOptions['port']);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);

            return $cache;
        });

        $app['doctrine.cache.factory.backing_memcached'] = $app->protect(function () {
            return new \Memcached();
        });

        $app['doctrine.cache.factory.memcached'] = $app->protect(function ($cacheOptions) use ($app) {
            throw new \RuntimeException('memcached not implemented');
            if (empty($cacheOptions['host']) || empty($cacheOptions['port'])) {
                throw new \RuntimeException('Host and port options need to be specified for memcached cache');
            }

            /** @var \Memcached $memcached */
            $memcached = $app['doctrine.orm.cache.factory.backing_memcached']();
            $memcached->addServer($cacheOptions['host'], $cacheOptions['port']);

            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);

            return $cache;
        });

        $app['doctrine.cache.factory.void'] = $app->protect(function () {
            return new VoidCache();
        });

        $app['doctrine.cache.factory.array'] = $app->protect(function () {
            return new ArrayCache();
        });

        $app['doctrine.cache.factory.php_file'] = $app->protect(function ($cacheOptions) {
            $directory = @$cacheOptions['directory'];
            $extension = @$cacheOptions['extension'];
            $umask = @$cacheOptions['umask'];

            if ($directory === null) {
                throw new \RuntimeException('FilesystemCache directory not defined');
            }

            $extension = $extension === null ? PhpFileCache::EXTENSION : $extension;
            $umask = $umask === null ? 0002 : $umask;

            return new PhpFileCache($directory, $extension, $umask);
        });

        $app['doctrine.cache.factory.apc'] = $app->protect(function () {
            return new ApcCache();
        });

        $app['doctrine.cache.factory.filesystem'] = $app->protect(function ($cacheOptions) {
            $directory = @$cacheOptions['directory'];
            $extension = @$cacheOptions['extension'];
            $umask = @$cacheOptions['umask'];

            if ($directory === null) {
                throw new \RuntimeException('FilesystemCache directory not defined');
            }

            $extension = $extension === null ? FilesystemCache::EXTENSION : $extension;
            $umask = $umask === null ? 0002 : $umask;

            return new FilesystemCache($directory, $extension, $umask);
        });

        $app['doctrine.cache.factory'] = $app->protect(function ($cacheOptions) use ($app) {
            $driver = $cacheOptions['type'];

            $cacheFactoryKey = 'doctrine.cache.factory.'.$driver;

            if (!isset($app[$cacheFactoryKey])) {
                throw new \RuntimeException("Factory '{$cacheFactoryKey}' for cache type '$driver' not defined (is it spelled correctly?)");
            }

            return $app[$cacheFactoryKey]($cacheOptions);
        });

        $app['doctrine.cache.options.initializer']();

        foreach ($app['doctrine.cache.options']['aliases'] as $name => $alias) {
            $options = @$app['doctrine.cache.options']['providers'][$name];
            if (is_array($options)) {
                $app[$alias] = function ($app) use ($name, $options) {
                    $cache = $app['doctrine.cache.locator']($name, $options);

                    return $cache;
                };
            }
        }

        $app['doctrine.caches'] = function ($app) {
            $caches = new Container();

            $aliases = $app['doctrine.cache.options']['aliases'];

            foreach ($app['doctrine.cache.options']["providers"] as $name => $options) {
                if (isset($aliases['name'])) {
                    $cache = $app[$aliases['name']];
                } else {
                    $cache = $app['doctrine.cache.locator']($name, $options);
                }

                $caches[$name] = $cache;
            }

            return $caches;
        };
    }
}
