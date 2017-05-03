<?php

namespace SearchQueryParser\Part;

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
     * @param bool $negate
     */
    public function __construct($negate = false)
    {
        $this->negate = $negate;
    }

    /**
     * @return bool
     */
    public function isNegated()
    {
        return $this->negate;
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
     * @param int $index
     *
     * @return PartInterface
     */
    public function getPart($index)
    {
        if (isset($this->parts[$index])) {
            return $this->parts[$index];
        }

        throw new \OverflowException('Invalid part index');
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
