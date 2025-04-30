<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

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
