<?php

namespace Tests\Unit\DataMapper\Parsers;

use App\DataMapper\Parsers\SyntaxParser;
use Tests\TestCase;

/**
 * @class SyntaxParserTest
 * Test syntax parser
 */
class SyntaxParserTest extends TestCase
{
    private SyntaxParser $syntaxParser;
    public function setUp(): void
    {
        parent::setUp();
        $this->syntaxParser = $this->app->make(SyntaxParser::class);
    }
}
