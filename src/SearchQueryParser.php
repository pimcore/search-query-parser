<?php

declare(strict_types=1);

namespace SearchQueryParser;

use SearchQueryParser\Part\Query;

/**
 * Simple facade hiding away lexer and parser
 */
class SearchQueryParser
{
    /**
     * @param string $query
     * @param LexerInterface|null $lexer
     * @param ParserInterface|null $parser
     *
     * @return Query
     */
    public static function parseQuery($query, LexerInterface $lexer = null, ParserInterface $parser = null): Query
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
}
