<?php

namespace Query\Part;

class Query implements PartInterface
{
    /**
     * @var PartInterface[]
     */
    protected $parts = [];

    /**
     * @var bool
     */
    protected $negate = false;

    /**
     * @param string $token The opening brace token
     */
    public function __construct($token = null)
    {
        if (null !== $token) {
            if (strpos($token, '!') !== false) {
                $this->negate = true;
            }
        }
    }

    /**
     * @param PartInterface $part
     */
    public function addPart(PartInterface $part)
    {
        $this->parts[] = $part;
    }

    /**
     * @return PartInterface[]
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @return PartInterface
     */
    public function getLastPart()
    {
        if (!empty($this->parts)) {
            return $this->parts[count($this->parts) - 1];
        }
    }
}
