<?php

namespace Query\Part;

class Identifier implements PartInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var bool
     */
    protected $negate = false;

    /**
     * @param string $identifier
     * @param bool $negate
     */
    public function __construct($identifier, $negate = false)
    {
        $this->identifier = $identifier;
        $this->negate     = (bool)$negate;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return bool
     */
    public function isNegated()
    {
        return $this->negate;
    }
}
