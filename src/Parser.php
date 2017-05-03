<?php

declare(strict_types=1);

namespace SearchQueryParser;

use SearchQueryParser\Part\Keyword;
use SearchQueryParser\Part\Query;
use SearchQueryParser\Part\Term;

class Parser implements ParserInterface
{
    /**
     * @param Token[] $tokens
     *
     * @return Query
     */
    public function parse(array $tokens): Query
    {
        $query = new Query();

        /** @var Query[] $queryStack */
        $queryStack   = [];
        $currentQuery = $query;

        /** @var Token $previousToken */
        $previousToken = null;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];

            if (!$token instanceof Token) {
                throw new \InvalidArgumentException(sprintf(
                    'Token at index %d must be of type Token, "%s" given',
                    $i,
                    is_object($token) ? get_class($token) : gettype($token)
                ));
            }

            // brace open/close - sub-queries
            if ($token->isTypeOf(Tokens::T_BRACE_OPEN)) {
                array_push($queryStack, $currentQuery);

                $negated      = $this->isNegated($i, $tokens);
                $currentQuery = new Query($negated);
            }

            if ($token->isTypeOf(Tokens::T_BRACE_CLOSE)) {
                if (count($queryStack) === 0) {
                    throw new ParserException('Can\'t close sub query as query stack is empty');
                }

                $closingQuery = $currentQuery;
                $currentQuery = array_pop($queryStack);
                $currentQuery->addPart($closingQuery);
            }

            // terms (the actual values we're looking for)
            if (in_array($token->getToken(), Tokens::getTermTokens())) {
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
            if ($token->isTypeOf(Tokens::T_KEYWORD)) {
                if ($previousToken && $previousToken->isTypeOf(Tokens::T_KEYWORD)) {
                    throw new ParserException(sprintf(
                        'Keyword can\'t be succeeded by another keyword (%s %s)',
                        $previousToken->getContent(), $token->getContent()
                    ));
                }

                $currentQuery->addPart(new Keyword($token->getContent()));
            }

            $previousToken = $token;
        }

        return $query;
    }

    /**
     * Check if the token is fuzzy (is not quoted and contains *)
     *
     * @param Token $token
     * @param string $value
     *
     * @return bool
     */
    private function isFuzzy(Token $token, string $value): bool
    {
        if ($token->isTypeOf([Tokens::T_TERM_QUOTED, Tokens::T_TERM_QUOTED_SINGLE])) {
            return false;
        }

        return false !== strpos($value, '*');
    }

    /**
     * Check if expression was negated by looking back at previous tokens
     *
     * @param int $index
     * @param Token[] $tokens
     *
     * @return bool
     */
    private function isNegated(int $index, array $tokens): bool
    {
        $negated = false;

        $startIndex = $index - 1;
        if ($startIndex < 0) {
            return $negated;
        }

        for ($i = $startIndex; $i >= 0; $i--) {
            if ($tokens[$i]->isTypeOf(Tokens::T_NEGATION)) {
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
     * @param Token $token
     *
     * @return string
     */
    private function normalizeTerm(Token $token): string
    {
        $term = $token->getContent();

        if ($token->isTypeOf(Tokens::T_TERM_QUOTED)) {
            $term = preg_replace('/^"(.*)"$/', '$1', $term);
        } elseif ($token->isTypeOf(Tokens::T_TERM_QUOTED_SINGLE)) {
            $term = preg_replace('/^\'(.*)\'$/', '$1', $term);
        }

        return $term;
    }
}
