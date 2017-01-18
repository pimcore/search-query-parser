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
     */
    public function __construct($term)
    {
        $pattern = '/^([!@]+)/';
        if (preg_match($pattern, $term, $matches)) {
            $term = preg_replace($pattern, '', $term);

            // negated
            if (strpos($matches[0], '!') !== false) {
                $this->negate = true;
            }

            // strict comparison
            if (strpos($matches[0], '@') !== false) {
                $this->fuzzy = false;
            }
        }

        $this->term = $term;
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
