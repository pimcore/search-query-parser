<?php

namespace SearchQueryParser;

use Phlexy\Lexer;

interface LexerInterface extends Lexer
{
    const T_WHITESPACE = 0;
    const T_BRACE_OPEN = 1;
    const T_BRACE_CLOSE = 2;
    const T_KEYWORD = 3;
    const T_NEGATION = 4;
    const T_TERM = 5;
    const T_TERM_QUOTED = 6;
}
