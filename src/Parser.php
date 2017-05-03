<?php

namespace SearchQueryParser;

use SearchQueryParser\Part\Keyword;
use SearchQueryParser\Part\Query;
use SearchQueryParser\Part\Term;

class Parser implements ParserInterface
{
    /**
     * @param array $tokens
     *
     * @return Query
     */
    public function parse(array $tokens)
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

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            // brace open/close - sub-queries
            if ($token[0] === Lexer::T_BRACE_OPEN) {
                array_push($queryStack, $currentQuery);

                $negated      = $this->isNegated($i, $tokens);
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
                $fuzzy   = $this->isFuzzy($token, $value);
                $negated = $this->isNegated($i, $tokens);
                $term    = new Term($value, $fuzzy, $negated);

                // add an AND/OR before inserting the term if the last part was no keyword
                $lastPart = $currentQuery->getLastPart();
                if ($lastPart && !($lastPart instanceof Keyword)) {
                    $currentQuery->addPart(new Keyword('AND'));
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
     * Check if the token is fuzzy (is not quoted and contains *)
     *
     * @param array $token
     * @param string $value
     *
     * @return bool
     */
    protected function isFuzzy(array $token, $value): bool
    {
        if (in_array($token[0], [Lexer::T_TERM_QUOTED])) {
            return false;
        }

        return false !== strpos($value, '*');
    }

    /**
     * Check if expression was negated by looking back at previous tokens
     *
     * @param $index
     * @param array $tokens
     *
     * @return bool
     */
    protected function isNegated($index, array $tokens)
    {
        $negated = false;

        $startIndex = $index - 1;
        if ($startIndex < 0) {
            return $negated;
        }

        for ($i = $startIndex; $i >= 0; $i--) {
            if ($tokens[$i][0] === Lexer::T_NEGATION) {
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
     *
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
