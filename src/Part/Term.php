<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

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
