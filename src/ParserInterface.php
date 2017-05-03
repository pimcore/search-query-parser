<?php

declare(strict_types=1);

namespace SearchQueryParser;

use SearchQueryParser\Part\Query;

interface ParserInterface
{
    /**
     * @param Token[] $tokens
     *
     * @return Query
     */
    public function parse(array $tokens): Query;
}
