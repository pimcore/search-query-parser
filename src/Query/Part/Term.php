<?php

namespace Query\Part;

class Term implements PartInterface
{
    /**
     * @var string
     */
    protected $term;

    /**
     * @var bool
     */
    protected $negate = false;

    /**
     * @var bool
     */
    protected $fuzzy = true;

    /**
     * @param string $term
     * @param bool $fuzzy
     * @param bool $negate
     */
    public function __construct($term, $fuzzy = true, $negate = false)
    {
        $this->term   = $term;
        $this->fuzzy  = $fuzzy;
        $this->negate = $negate;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @return bool
     */
    public function isNegated()
    {
        return $this->negate;
    }

    /**
     * @return bool
     */
    public function isFuzzy()
    {
        return $this->fuzzy;
    }
}
