<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

class WhatChangedContainerFactory
{
    const KEY_LOCK1 = 'lock_path';
    const KEY_LOCK2 = 'compare_lock_path';
    const KEY_CACHE_PATH = 'cache_path';
    const KEY_GITHUB_OAUTH = 'github_oauth_token';
    const KEY_MAX_COMMITS = 'max_commits';
    const KEY_MAX_REPOS = 'max_repos';

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
            self::KEY_GITHUB_OAUTH => null,
            self::KEY_MAX_COMMITS => null,
            self::KEY_MAX_REPOS => null,
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
            $config[self::KEY_CACHE_PATH],
            $config[self::KEY_GITHUB_OAUTH],
            $config[self::KEY_MAX_COMMITS],
            $config[self::KEY_MAX_REPOS],
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
