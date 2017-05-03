<?php

declare(strict_types=1);

namespace SearchQueryParser\Part;

final class Query implements PartInterface
{
    /**
     * @var PartInterface[]
     */
    private $parts = [];

    /**
     * @var bool
     */
    private $negate = false;

    /**
     * @param bool $negate
     */
    public function __construct(bool $negate = false)
    {
        $this->negate = $negate;
    }

    /**
     * @return bool
     */
    public function isNegated(): bool
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
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param int $index
     *
     * @return PartInterface
     */
    public function getPart(int $index): PartInterface
    {
        if (isset($this->parts[$index])) {
            return $this->parts[$index];
        }

        throw new \OverflowException('Invalid part index');
    }

    /**
     * @return PartInterface|null
     */
    public function getLastPart()
    {
        if (!empty($this->parts)) {
            return $this->parts[count($this->parts) - 1];
        }
    }
}
