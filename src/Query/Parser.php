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

        $termTokens = [
            Lexer::T_TERM,
            Lexer::T_TERM_QUOTED
        ];

        for ($i = 0; $i < count($this->tokens); $i++) {
            $token = $this->tokens[$i];

            // brace open/close - sub-queries
            if ($token[0] === Lexer::T_BRACE_OPEN) {
                array_push($queryStack, $currentQuery);

                $negated      = $this->isNegated($i);
                $currentQuery = new Query($negated);
            }

            if ($token[0] === Lexer::T_BRACE_CLOSE) {
                if (count($queryStack) === 0) {
                    throw new ParserException('Can\'t close sub query as query stack is empty');
                }

                $closingQuery = $currentQuery;
                $currentQuery = array_pop($queryStack);
                $currentQuery->addPart($closingQuery);
            }

            // terms (the actual values we're looking for)
            if (in_array($token[0], $termTokens)) {
                $value   = $this->normalizeTerm($token);
                $fuzzy   = $token[0] !== Lexer::T_TERM_QUOTED;
                $negated = $this->isNegated($i);
                $term    = new Term($value, $fuzzy, $negated);

                // add an AND/OR before inserting the term if the last part was no keyword
                $lastPart = $currentQuery->getLastPart();
                if ($lastPart && !($lastPart instanceof Keyword)) {
                    if ($term->isNegated()) {
                        $currentQuery->addPart(new Keyword('AND'));
                    } else {
                        $currentQuery->addPart(new Keyword('OR'));
                    }
                }

                $lastPart = $currentQuery->getLastPart();
                if (null !== $lastPart && !($lastPart instanceof Keyword)) {
                    throw new ParserException(sprintf(
                        'Expected a keyword (AND/OR), but found a %s',
                        (new \ReflectionClass($lastPart))->getShortName()
                    ));
                }

                $currentQuery->addPart($term);
            }

            // keywords (AND, OR)
            if ($token[0] === Lexer::T_KEYWORD) {
                if ($previousToken && $previousToken[0] === Lexer::T_KEYWORD) {
                    throw new ParserException(sprintf(
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

    /**
     * Check if expression was negated by looking back at previous tokens
     *
     * @param $index
     * @return bool
     */
    protected function isNegated($index)
    {
        $negated = false;

        $startIndex = $index - 1;
        if ($startIndex < 0) {
            return $negated;
        }

        for ($i = $startIndex; $i >= 0; $i--) {
            if ($this->tokens[$i][0] === Lexer::T_NEGATION) {
                $negated = !$negated;
            } else {
                break;
            }
        }

        return $negated;
    }

    /**
     * Normalize term (strip quotes)
     *
     * @param array $token
     * @return string
     */
    protected function normalizeTerm(array $token)
    {
        $term = $token[2];
        if ($token[0] === Lexer::T_TERM_QUOTED) {
            $term = preg_replace('/^"(.*)"$/', '$1', $term);
        }

        return $term;
    }
}
