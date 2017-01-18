<?php

namespace Query;

use Query\Part\Term;
use Query\Part\Keyword;
use Query\Part\Query;

class Parser
{
    protected $tokens = [];

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
    }

    /**
     * @return Query
     */
    public function parse()
    {
        $query = new Query();

        /** @var Query[] $queryStack */
        $queryStack   = [];
        $currentQuery = $query;

        $previousToken = null;

        $keywords = [
            Lexer::T_AND,
            Lexer::T_OR
        ];

        foreach ($this->tokens as $token) {
            // brace open/close - sub-queries
            if ($token[0] === Lexer::T_BRACE_OPEN) {
                array_push($queryStack, $currentQuery);
                $currentQuery = new Query($token[2]);
            }

            if ($token[0] === Lexer::T_BRACE_CLOSE) {
                if (count($queryStack) === 0) {
                    throw new ParseException('Can\'t close sub query as query stack is empty');
                }

                $closingQuery = $currentQuery;
                $currentQuery = array_pop($queryStack);
                $currentQuery->addPart($closingQuery);
            }

            // terms (the actual values we're looking for)
            if ($token[0] === Lexer::T_TERM) {
                $term = new Term($token[2]);

                // add an AND/OR before inserting the term if the last part was no keyword
                $lastPart = $currentQuery->getLastPart();
                if (!($lastPart instanceof Keyword)) {
                    if ($term->isNegated()) {
                        $currentQuery->addPart(new Keyword('AND'));
                    } else {
                        $currentQuery->addPart(new Keyword('OR'));
                    }
                }

                $lastPart = $currentQuery->getLastPart();
                if (null !== $lastPart && !($lastPart instanceof Keyword)) {
                    throw new ParseException(sprintf(
                        'Expected a keyword (AND/OR), but found a %s',
                        (new \ReflectionClass($lastPart))->getShortName()
                    ));
                }

                $currentQuery->addPart($term);
            }

            // keywords (AND, OR)
            if (in_array($token[0], $keywords)) {
                if ($previousToken && in_array($previousToken[0], $keywords)) {
                    throw new ParseException(sprintf(
                        'Keyword can\'t be succeeded by another keyword (%s %s)',
                        $previousToken[2], $token[2]
                    ));
                }

                $currentQuery->addPart(new Keyword($token[2]));
            }

            $previousToken = $token;
        }

        return $query;
    }
}
