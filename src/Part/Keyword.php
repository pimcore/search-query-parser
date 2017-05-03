<?php

declare(strict_types=1);

namespace SearchQueryParser\Part;

final class Keyword implements PartInterface
{
    /**
     * @var string
     */
    private $keyword;

    /**
     * @param string $keyword
     */
    public function __construct(string $keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return string
     */
    public function getKeyword(): string
    {
        return $this->keyword;
    }
}
