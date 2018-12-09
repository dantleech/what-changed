<?php

namespace DTL\WhatChanged;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

class WhatChangedContainerFactory
{
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
            'limit' => 1,
            'cwd' => getcwd(),
        ];
        $config = array_merge($defaults, $this->config, $config);
        $this->require($config, ['cwd']);

        return new WhatChangedContainer(
            $config['cwd'],
            $config['limit']
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
            implode('", "', $diff), implode('", "', array_keys($config))
        ));
    }
}
