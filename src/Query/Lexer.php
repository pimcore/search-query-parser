<?php

namespace Query;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Lexer
{
    const T_BRACE_OPEN = 0;
    const T_BRACE_CLOSE = 1;
    const T_AND = 2;
    const T_OR = 3;
    const T_NOT = 4;
    const T_INVERSE_IDENTIFIER = 5;
    const T_IDENTIFIER = 6;

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
            '\('  => static::T_BRACE_OPEN,
            '\)'  => static::T_BRACE_CLOSE,
            'AND' => static::T_AND,
            'OR'  => static::T_OR,
            'NOT' => static::T_NOT
        ];

        // The "i" is an additional modifier (all createLexer methods accept it)
        $this->lexer = $factory->createLexer($definition, 'i');
    }

    public function getLexer()
    {
        return $this->lexer;
    }
}
