<?php

namespace DTL\WhatChanged\Tests\Unit\Adapter\Github;

use DTL\WhatChanged\Adapter\Github\GithubUrlParser;
use DTL\WhatChanged\Exception\WhatChangedRuntimeException;
use PHPUnit\Framework\TestCase;

class GithubUrlParserTest extends TestCase
{
    /**
     * @var GithubUrlParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new GithubUrlParser();
    }

    /**
     * @dataProvider provideParse
     */
    public function testParse(string $url, array $expected)
    {
        $this->assertEquals($expected, $this->parser->parse($url));
    }

    public function provideParse()
    {
        yield [
            'https://github.com/phpactor/phpactor.git',
            [
                'phpactor',
                'phpactor',
            ]
        ];
    }

    public function testThrowsExceptionIfCannotParse()
    {
        $this->expectException(WhatChangedRuntimeException::class);
        $this->parser->parse('abcd');
    }
}
