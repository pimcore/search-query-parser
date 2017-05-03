<?php

declare(strict_types=1);

namespace SearchQueryParser\Test;

use PHPUnit\Framework\TestCase;
use SearchQueryParser\Lexer;
use SearchQueryParser\Tokens;

class LexerTest extends TestCase
{
    /**
     * @var Lexer
     */
    protected $lexer;

    protected function setUp()
    {
        $this->lexer = new Lexer();
    }

    public function testWhitespaceIsFiltered()
    {
        $input  = 'foo AND bar OR (baz)';
        $tokens = $this->lexer->lex($input);

        $whitespaceTokens = [];
        foreach ($tokens as $token) {
            if ($token->isTypeOf(Tokens::T_WHITESPACE)) {
                $whitespaceTokens[] = $token;
            }
        }

        $this->assertEmpty($whitespaceTokens, 'No whitespace tokens are found');
    }

    public function testSimpleTerm()
    {
        $tokens = $this->lexer->lex('foo');

        $this->assertCount(1, $tokens);
        $this->assertEquals(Tokens::T_TERM, $tokens[0]->getToken());
    }

    public function testNegatedTerm()
    {
        $tokens = $this->lexer->lex('!foo');

        $this->assertCount(2, $tokens);
        $this->assertEquals(Tokens::T_NEGATION, $tokens[0]->getToken());
        $this->assertEquals(Tokens::T_TERM, $tokens[1]->getToken());
    }
}
