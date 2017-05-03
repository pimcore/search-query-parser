<?php

declare(strict_types=1);

namespace SearchQueryParser;

use Phlexy\Lexer as PhlexyLexer;

interface LexerInterface extends PhlexyLexer
{
    /**
     * Transform input string into tokens
     *
     * @param $string
     *
     * @return Token[]
     */
    public function lex($string): array;
}
