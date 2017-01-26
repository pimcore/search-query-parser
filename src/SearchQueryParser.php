<?php

namespace SearchQueryParser;

/**
 * Simple facade hiding away lexer and parser
 */
class SearchQueryParser
{
    /**
     * @param $query
     * @param LexerInterface|null $lexer
     * @param ParserInterface|null $parser
     * @return Part\Query
     */
    public static function parseQuery($query, LexerInterface $lexer = null, ParserInterface $parser = null)
    {
        if (null === $lexer) {
            $lexer = new Lexer();
        }

        if (null === $parser) {
            $parser = new Parser();
        }

        $tokens = $lexer->lex($query);
        $query  = $parser->parse($tokens);

        return $query;
    }

    // static class
    private final function __construct()
    {
    }

    private final function __clone()
    {
    }
}
