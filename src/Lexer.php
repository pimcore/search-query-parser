<?php

namespace SearchQueryParser;

use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Lexer implements LexerInterface
{
    /**
     * @var \Phlexy\Lexer
     */
    protected $lexer;

    /**
     * @param \Phlexy\Lexer|null $lexer
     */
    public function __construct(\Phlexy\Lexer $lexer = null)
    {
        if (null === $lexer) {
            $this->lexer = $this->buildDefaultLexer();
        } else {
            $this->lexer = $lexer;
        }
    }

    /**
     * @return array
     */
    protected function getDefaultDefinition()
    {
        return [
            '\('           => static::T_BRACE_OPEN,
            '\)'           => static::T_BRACE_CLOSE,
            '(AND|OR)'     => static::T_KEYWORD,
            '!'            => static::T_NEGATION,
            '"[^"]+"'      => static::T_TERM_QUOTED,
            '[^\s!@\(\)]+' => static::T_TERM,
            '\s+'          => static::T_WHITESPACE,
        ];
    }

    /**
     * @return \Phlexy\Lexer
     */
    protected function buildDefaultLexer()
    {
        $factory = new UsingPregReplace(
            new LexerDataGenerator()
        );

        $definition = $this->getDefaultDefinition();

        // The "i" is an additional modifier (all createLexer methods accept it)
        return $factory->createLexer($definition, 'i');
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

        // make sure we return a numerically indexed array
        return array_values($tokens);
    }
}
