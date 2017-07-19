Doctrine cache provider for Silex framework
=======

[![Build Status](https://travis-ci.org/SinSquare/silex-doctrine-cache-provider.svg?branch=master)](https://travis-ci.org/SinSquare/silex-doctrine-cache-provider)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4a2cacf8-9f49-43e3-8a36-6f3c4834835c/big.png)](https://insight.sensiolabs.com/projects/4a2cacf8-9f49-43e3-8a36-6f3c4834835c)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/9d24d9a01c994a5e940cdf64defa8cf5)](https://www.codacy.com/app/SinSquare/silex-doctrine-cache-provider?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=SinSquare/silex-doctrine-cache-provider&amp;utm_campaign=Badge_Grade)

Installation
============

With composer :

``` json
{
    "require": {
        "sinsquare/silex-doctrine-cache-provider": "1.*"
    }
}
```

Usage
=====

You can use the provider with Pimple or Silex.

First you have to create the condig for the cache, than you have to register the DoctrineCacheServiceProvider.

```php
<?php

use SinSquare\Cache\DoctrineCacheServiceProvider;

$container = new Container();
$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache_name_1' => array(
            'type' => 'void',
        ),
    ),
);

$container->register(new DoctrineCacheServiceProvider());
```

To access a cache, you can either get if with the $container['doctrine.caches'][<cache name>] or with $container['doctrine.cache.<cache_name>']

```php
<?php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache_name_1' => array(
            'type' => 'void',
        ),
    ),
);

$container->register(new DoctrineCacheServiceProvider());

$cache = $container['doctrine.caches']['cache_name_1'];
//OR
$cache = $container['doctrine.caches.cache_name_1'];

```

Options
====

Available cache types:

APC

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'apc'
        ),
    ),
);

```

APCu

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'apcu'
        ),
    ),
);

```

Array

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'array'
        ),
    ),
);

```

Chain

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'chain',
            'caches' => array('cache_1', 'cache_2')
        ),
        'cache_1' => array(
            'type' => 'array'
        ),
        'cache_2' => array(
            'type' => 'apc'
        ),
    ),
);

```

Filesystem

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'filesystem',
            'directory' => 'dir',
			'extension' => 'value' //optional
			'umask' => 'value' //optional
        )
    ),
);

```

Filesystem

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'filesystem',
            'directory' => 'dir',
			'extension' => 'value' //optional
			'umask' => 'value' //optional
        )
    ),
);

```

PHP file

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'phpfile',
            'directory' => 'dir',
			'extension' => 'value' //optional
			'umask' => 'value' //optional
        )
    ),
);

```

Memcache

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'memcache',
            'host' => 'host',
			'port' => 'port'
        )
    ),
);

```

Memcached

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'memcached',
            'host' => 'host',
			'port' => 'port'
        )
    ),
);

```

Void

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'void'
        )
    ),
);

```

Creating custom cache
====

For creating a new cache provider all you have to do is the following:
- create the new CacheType
- define the $container['doctrine.cache.factory.<new provider name>']
- use the new provider in the config


Creating the new Cache type

```php

<?php

namespace Your\Name\Space;

use Doctrine\Common\Cache\CacheProvider;

class MyCustomCache extends CacheProvider
{
//class body with the required methods and functionality
}

```

Create a factory for it

```php

//you have to define this BEFORE you get a new cache, preferably before registering the provider
$container['doctrine.cache.factory.customcache'] = $container->protect(function ($cacheOptions) use ($container) {

	$namespace = $cacheOptions["namespace"];

	//

	return new MyCustomCache();

});

```

Use it

```php

$container['doctrine.cache.options'] = array(
    'providers' => array(
        'cache' => array(
            'type' => 'customcache'
        )
    ),
);

//getting the cache is the same as before

```
