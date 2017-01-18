<?php


namespace Query;


use Query\Part\Identifier;
use Query\Part\Keyword;
use Query\Part\Query;

class Parser
{
    protected $tokens = [];

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
    }

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
                $currentQuery = new Query();
            }

            if ($token[0] === Lexer::T_BRACE_CLOSE) {
                if (count($queryStack) === 0) {
                    throw new ParseException('Can\'t close sub query as query stack is empty');
                }

                $closingQuery = $currentQuery;
                $currentQuery = array_pop($queryStack);
                $currentQuery->addPart($closingQuery);
            }

            // identifiers (the actual values we're looking for)
            if ($token[0] === Lexer::T_IDENTIFIER) {
                $negate = false;

                if ($previousToken) {
                    // AND-combine identifiers if no keyword is in between
                    if ($previousToken[0] === Lexer::T_IDENTIFIER) {
                        $currentQuery->addPart(new Keyword('AND'));
                    }

                    // negate identifier if there's an ! in front of it
                    if ($previousToken && $previousToken[0] === Lexer::T_NEGATION) {
                        $negate = true;
                    }
                }

                if ($currentQuery->getLastPart() instanceof Query) {
                    $currentQuery->addPart(new Keyword('AND'));
                }

                $lastPart = $currentQuery->getLastPart();
                if (null !== $lastPart && !($lastPart instanceof Keyword)) {
                    throw new ParseException('Identifiers need to be combined with a keyword');
                }

                $currentQuery->addPart(new Identifier($token[2], $negate));
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
