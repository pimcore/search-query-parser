<?php

declare(strict_types=1);

namespace SearchQueryParser;

final class Tokens
{
    const T_WHITESPACE = 0;
    const T_BRACE_OPEN = 1;
    const T_BRACE_CLOSE = 2;
    const T_KEYWORD = 3;
    const T_NEGATION = 4;
    const T_TERM = 5;
    const T_TERM_QUOTED = 6;
    const T_TERM_QUOTED_SINGLE = 7;

    /**
     * Get a token name from its value
     *
     * @param int $token
     *
     * @return string
     */
    public static function getName(int $token): string
    {
        $tokens = self::getTokenMapping();

        if (!isset($tokens[$token])) {
            throw new \InvalidArgumentException(sprintf('Token with value "%d" was not found', $token));
        }

        return $tokens[$token];
    }

    /**
     * Get tokens identifying term values
     *
     * @return array
     */
    public static function getTermTokens(): array
    {
        static $termTokens;

        if (null === $termTokens) {
            $termTokens = [
                self::T_TERM,
                self::T_TERM_QUOTED,
                self::T_TERM_QUOTED_SINGLE
            ];
        }

        return $termTokens;
    }

    private static function getTokenMapping(): array
    {
        static $constants;

        if (null === $constants) {
            $reflection = new \ReflectionClass(__CLASS__);
            $constants = array_flip($reflection->getConstants());
        }

        return $constants;
    }
}
