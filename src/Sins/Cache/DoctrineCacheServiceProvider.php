<?php

namespace SinSquare\Cache;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineCacheServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        //initialize and validate options
        $app['doctrine.cache.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['doctrine.cache.options'])) {
                return;
            }

            $tmp = $app['doctrine.cache.options'];

            if (!is_array($tmp['providers'])) {
                throw new \Exception("'doctrine.cache.options => providers' not set or not array, can't initialize!");
            } else {
                foreach ($tmp['providers'] as $name => &$options) {
                    if (!is_array($options)) {
                        throw new \Exception("'doctrine.cache.options => providers => {$name}' not set or not array, can't initialize!");
                    } elseif (!isset($options['type']) || !is_string($options['type'])) {
                        throw new \Exception("'doctrine.cache.options => providers => {$name} => type' not set or not string, can't initialize!");
                    }
                }
            }

            if (!isset($tmp['aliases'])) {
                $tmp['aliases'] = array();
            } elseif (!is_array($tmp['aliases'])) {
                throw new \Exception("'doctrine.cache.options => aliases' not array, can't initialize!");
            } else {
                foreach ($tmp['aliases'] as $name => $alias) {
                    if (!is_string($name)) {
                        throw new \Exception("'doctrine.cache.options => aliases' contains not string name, can't initialize!");
                    } elseif (!is_string($alias)) {
                        throw new \Exception("'doctrine.cache.options => aliases' contains not string alias, can't initialize!");
                    } elseif (!isset($tmp['providers'][$name])) {
                        throw new \Exception("'doctrine.cache.options => aliases => {$name}' no provider found, can't initialize!");
                    }
                }
            }

            $app['doctrine.cache.options'] = $tmp;
        });

        //cache locator
        $app['doctrine.cache.locator'] = $app->protect(function ($cacheName, $options) use ($app) {
            if (!isset($options['type'])) {
                throw new \RuntimeException("No type specified for '{$cacheName}'");
            }

            $cache = $app['doctrine.cache.factory']($options);

            if (isset($options['namespace']) && $cache instanceof CacheProvider) {
                $cache->setNamespace($options['namespace']);
            }

            return $cache;
        });

        $app['doctrine.cache.factory.apc'] = $app->protect(function ($cacheOptions) {
            return new ApcCache();
        });

        $app['doctrine.cache.factory.apcu'] = $app->protect(function ($cacheOptions) {
            return new ApcuCache();
        });

        $app['doctrine.cache.factory.array'] = $app->protect(function ($cacheOptions) {
            return new ArrayCache();
        });

        $app['doctrine.cache.factory.chain'] = $app->protect(function ($cacheOptions) use ($app) {
            if (empty($cacheOptions['caches']) || !is_array($cacheOptions['caches'])) {
                throw new \RuntimeException('Host and port options need to be specified for memcache cache');
            }

            $caches = array();

            foreach ($cacheOptions['caches'] as $cacheName) {
                $caches[] = $app['doctrine.cache.'.$cacheName];
            }

            return new ChainCache($caches);
        });

        $app['doctrine.cache.factory.filesystem'] = $app->protect(function ($cacheOptions) {
            $directory = @$cacheOptions['directory'];
            $extension = @$cacheOptions['extension'];
            $umask = @$cacheOptions['umask'];

            if (empty($cacheOptions['directory'])) {
                throw new \RuntimeException('FilesystemCache directory not defined');
            }

            $extension = $extension === null ? FilesystemCache::EXTENSION : $extension;
            $umask = $umask === null ? 0002 : $umask;

            return new FilesystemCache($directory, $extension, $umask);
        });

        $app['doctrine.cache.factory.backing_memcache'] = $app->protect(function () {
            return new \Memcache();
        });

        $app['doctrine.cache.factory.memcache'] = $app->protect(function ($cacheOptions) {
            if (empty($cacheOptions['host']) || empty($cacheOptions['port'])) {
                throw new \RuntimeException('Host and port options need to be specified for memcache cache');
            }

            /** @var \Memcache $memcache */
            $memcache = $app['doctrine.cache.factory.backing_memcache']();
            $memcache->connect($cacheOptions['host'], $cacheOptions['port']);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);

            return $cache;
        });

        $app['doctrine.cache.factory.backing_memcached'] = $app->protect(function () {
            return new \Memcached();
        });

        $app['doctrine.cache.factory.memcached'] = $app->protect(function ($cacheOptions) {
            if (empty($cacheOptions['host']) || empty($cacheOptions['port'])) {
                throw new \RuntimeException('Host and port options need to be specified for memcached cache');
            }

            /** @var \Memcached $memcached */
            $memcached = $app['doctrine.cache.factory.backing_memcached']();
            $memcached->addServer($cacheOptions['host'], $cacheOptions['port']);

            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);

            return $cache;
        });

        $app['doctrine.cache.factory.phpfile'] = $app->protect(function ($cacheOptions) {
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

        $app['doctrine.cache.factory.void'] = $app->protect(function ($cacheOptions) {
            return new VoidCache();
        });

        $app['doctrine.cache.factory'] = $app->protect(function ($cacheOptions) use ($app) {
            $driver = $cacheOptions['type'];

            $cacheFactoryKey = 'doctrine.cache.factory.'.$driver;

            if (!isset($app[$cacheFactoryKey])) {
                throw new \RuntimeException("Factory '{$cacheFactoryKey}' for cache type '$driver' not defined (is it spelled correctly?)");
            }

            return $app[$cacheFactoryKey]($cacheOptions);
        });

        //initilazie cache config
        $app['doctrine.cache.options.initializer']();
        if(empty($app['doctrine.cache.options'])) {
            return;
        }
        //initilaize cache aliases
        foreach ($app['doctrine.cache.options']['aliases'] as $name => $alias) {
            $options = @$app['doctrine.cache.options']['providers'][$name];
            if (is_array($options)) {
                $app[$alias] = function ($app) use ($name, $options) {
                    $cache = $app['doctrine.caches'][$name];

                    return $cache;
                };
            }
        }
        //initilaize caches
        foreach ($app['doctrine.cache.options']['providers'] as $name => $options) {
            $app['doctrine.cache.'.$name] = function () use ($app, $name, $options) {
                return $app['doctrine.cache.locator']($name, $options);
            };
        }
        //cache container
        $app['doctrine.caches'] = function ($app) {
            $caches = new Container();

            foreach ($app['doctrine.cache.options']['providers'] as $name => $options) {
                $caches[$name] = function () use ($app, $name) {
                    return $app['doctrine.cache.'.$name];
                };
            }

            return $caches;
        };
    }
}
