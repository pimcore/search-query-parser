<?php

namespace Query\Part;

class Keyword implements PartInterface
{
    /**
     * @var string
     */
    protected $keyword;

    /**
     * @param string $keyword
     */
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}
