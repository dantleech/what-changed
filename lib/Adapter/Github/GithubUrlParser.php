<?php

namespace DTL\WhatChanged\Adapter\Github;

use DTL\WhatChanged\Exception\WhatChangedRuntimeException;

class GithubUrlParser
{
    public function parse(string $url)
    {
        if (!preg_match('{github.com/(.*?)/(.+?)\.}', $url, $matches)) {
            throw new WhatChangedRuntimeException(sprintf(
                'Could not parse Github URL "%s"',
                $url
            ));
        }

        array_shift($matches);
        return array_values($matches);
    }
}
