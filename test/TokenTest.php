<?php

declare(strict_types=1);

namespace SearchQueryParser\Test;

use PHPUnit\Framework\TestCase;
use SearchQueryParser\Token;
use SearchQueryParser\Tokens;

class TokenTest extends TestCase
{
    public function testIsTypeOfScalar()
    {
        $token = new Token(Tokens::T_TERM, 1, 'foo');

        $this->assertTrue($token->isTypeOf(Tokens::T_TERM));
        $this->assertFalse($token->isTypeOf(Tokens::T_TERM_QUOTED));
        $this->assertFalse($token->isTypeOf(Tokens::T_TERM_QUOTED_SINGLE));
        $this->assertFalse($token->isTypeOf(Tokens::T_KEYWORD));
    }

    public function testIsTypeOfArray()
    {
        $token = new Token(Tokens::T_TERM, 1, 'foo');

        $this->assertTrue($token->isTypeOf([Tokens::T_TERM]));
        $this->assertTrue($token->isTypeOf([Tokens::T_TERM, Tokens::T_TERM_QUOTED, Tokens::T_TERM_QUOTED_SINGLE]));
        $this->assertFalse($token->isTypeOf([Tokens::T_TERM_QUOTED, Tokens::T_TERM_QUOTED_SINGLE]));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnInvalidValue()
    {
        new Token(999999999, 1, 'foo');
    }
}
