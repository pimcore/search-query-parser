<?php

declare(strict_types=1);

namespace SearchQueryParser;

use Phlexy\Lexer as PhlexyLexer;
use Phlexy\LexerDataGenerator;
use Phlexy\LexerFactory\Stateless\UsingPregReplace;

class Lexer implements LexerInterface
{
    /**
     * @var PhlexyLexer
     */
    protected $lexer;

    /**
     * @param PhlexyLexer|null $lexer
     */
    public function __construct(PhlexyLexer $lexer = null)
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
    protected function getDefaultDefinition(): array
    {
        return [
            '\('           => Tokens::T_BRACE_OPEN,
            '\)'           => Tokens::T_BRACE_CLOSE,
            '(AND|OR)'     => Tokens::T_KEYWORD,
            '!'            => Tokens::T_NEGATION,
            '"[^"]+"'      => Tokens::T_TERM_QUOTED,
            "'[^']+'"      => Tokens::T_TERM_QUOTED_SINGLE,
            '[^\s!\(\)]+'  => Tokens::T_TERM,
            '\s+'          => Tokens::T_WHITESPACE,
        ];
    }

    /**
     * @return PhlexyLexer
     */
    protected function buildDefaultLexer(): PhlexyLexer
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
     *
     * @return Token[]
     */
    public function lex($string): array
    {
        $tokens = $this->lexer->lex($string);

        // ignore whitespace
        $tokens = array_filter($tokens, function ($token) {
            return $token[0] !== Tokens::T_WHITESPACE;
        });

        // transform arrays into token objects
        /** @var Token[] $tokens */
        $tokens = array_map(function (array $token) {
            return new Token($token[0], $token[1], $token[2]);
        }, $tokens);

        // make sure we return a numerically indexed array
        return array_values($tokens);
    }
}
