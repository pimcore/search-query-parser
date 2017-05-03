<?php

declare(strict_types=1);

namespace SearchQueryParser\Part;

final class Term implements PartInterface
{
    /**
     * @var string
     */
    private $term;

    /**
     * @var bool
     */
    private $negate = false;

    /**
     * @var bool
     */
    private $fuzzy = true;

    /**
     * @param string $term
     * @param bool $fuzzy
     * @param bool $negate
     */
    public function __construct(string $term, bool $fuzzy = true, bool $negate = false)
    {
        $this->term   = $term;
        $this->fuzzy  = $fuzzy;
        $this->negate = $negate;
    }

    /**
     * @return string
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * @return bool
     */
    public function isNegated(): bool
    {
        return $this->negate;
    }

    /**
     * @return bool
     */
    public function isFuzzy(): bool
    {
        return $this->fuzzy;
    }
}
