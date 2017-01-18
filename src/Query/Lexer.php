<?php

namespace Query;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Lexer implements \Phlexy\Lexer
{
    const T_BRACE_OPEN = 0;
    const T_BRACE_CLOSE = 1;
    const T_AND = 2;
    const T_OR = 3;
    const T_TERM = 4;
    const T_WHITESPACE = 5;

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
            'AND'               => static::T_AND,
            'OR'                => static::T_OR,
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
        $tokens = array_filter($tokens, function ($token) {
            return $token[0] !== static::T_WHITESPACE;
        });

        return $tokens;
    }
}
