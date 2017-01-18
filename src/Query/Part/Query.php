<?php

namespace Query\Part;

class Query implements PartInterface
{
    /**
     * @var PartInterface[]
     */
    protected $parts = [];

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
