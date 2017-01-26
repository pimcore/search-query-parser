<?php

namespace SearchQueryParser;

use SearchQueryParser\Part\Query;

interface ParserInterface
{
    /**
     * @param array $tokens
     * @return Query
     */
    public function parse(array $tokens);
}
