<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

class WhatChangedContainerFactory
{
    const KEY_LOCK1 = 'lock_path';
    const KEY_LOCK2 = 'compare_lock_path';
    const KEY_CACHE_PATH = 'cache_path';


    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(array $config = []): WhatChangedContainer
    {
        $defaults = [
            self::KEY_LOCK1 => getcwd() . '/composer.lock',
            self::KEY_LOCK2 => getcwd() . '/vendor/composer/what-changed/composer.lock.old',
            self::KEY_CACHE_PATH => getcwd() . '/vendor/composer/what-changed/cache',
        ];

        $config = array_merge($defaults, $this->config, $config);

        if ($diff = array_diff(array_keys($config), array_keys($defaults))) {
            throw new WhatChangedRuntimeException(sprintf(
                'Invalid configuration keys "%s", known keys: "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($defaults))
            ));
        }

        return new WhatChangedContainer(
            $config[self::KEY_LOCK1],
            $config[self::KEY_LOCK2],
            $config[self::KEY_CACHE_PATH]
        );
    }

    private function require(array $config, array $keys)
    {
        $diff = array_diff($keys, array_keys($config));

        if (empty($diff)) {
            return;
        }

        throw new WhatChangedRuntimeException(sprintf(
            'Configuration keys "%s" are required in config with keys "%s"',
            implode('", "', $diff),
            implode('", "', array_keys($config))
        ));
    }
}
