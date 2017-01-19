<?php

namespace Query;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Lexer implements \Phlexy\Lexer
{
    const T_WHITESPACE = 0;
    const T_BRACE_OPEN = 1;
    const T_BRACE_CLOSE = 2;
    const T_KEYWORD = 3;
    const T_TERM = 4;

    /**
     * @var \Phlexy\Lexer
     */
    protected $lexer;

    public function __construct()
    {
        $this->buildLexer();
    }

    protected function buildLexer()
    {
        $factory = new UsingPregReplace(
            new LexerDataGenerator()
        );

        $definition = [
            '!?\('              => static::T_BRACE_OPEN,
            '\)'                => static::T_BRACE_CLOSE,
            '(AND|OR)'          => static::T_KEYWORD,
            '[!@]*[^\s!@\(\)]+' => static::T_TERM,
            '\s+'               => static::T_WHITESPACE,
        ];

        // The "i" is an additional modifier (all createLexer methods accept it)
        $this->lexer = $factory->createLexer($definition, 'i');
    }

    /**
     * @param $string
     * @return array
     */
    public function lex($string)
    {
        $tokens = $this->lexer->lex($string);

        // ignore whitespace
        $tokens = array_filter($tokens, function ($token) {
            return $token[0] !== static::T_WHITESPACE;
        });

        return $tokens;
    }
}
