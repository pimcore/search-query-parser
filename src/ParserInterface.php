<?php

namespace SearchQueryParser;

use SearchQueryParser\Part\Query;

interface ParserInterface
{
    /**
     * @param Token[] $tokens
     *
     * @return Query
     */
    public function parse(array $tokens);
}
