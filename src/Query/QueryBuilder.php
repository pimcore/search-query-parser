<?php

namespace Query;

use Query\Part\Identifier;
use Query\Part\Keyword;
use Query\Part\PartInterface;
use Query\Part\Query;

class QueryBuilder
{
    const COMPARISON_EQUALS = 'equals';
    const COMPARISON_LIKE = 'like';

    /**
     * @var \Zend_Db_Select
     */
    protected $select;

    /**
     * @var Query
     */
    protected $query;

    /**
     * @var array
     */
    protected $fields = [
        'foo',
        'bar',
        'bazinga'
    ];

    /**
     * @var string
     */
    protected $comparison = self::COMPARISON_EQUALS;

    /**
     * @param \Zend_Db_Select $select
     * @param Query $query
     */
    public function __construct(\Zend_Db_Select $select, Query $query)
    {
        $this->select = $select;
        $this->query  = $query;
    }

    public function getQuery()
    {
        $this->processQuery($this->select, $this->query);

        return $this->select;
    }

    protected function processQuery(\Zend_Db_Select $select, Query $query)
    {
        $previousPart = null;

        /** @var Keyword[] $keywordStack */
        $keywordStack = [];

        foreach ($query->getParts() as $part) {
            if ($part instanceof Keyword) {
                array_push($keywordStack, $part);
                continue;
            }

            /** @var \Zend_Db_Select $subQuery */
            $subQuery = null;

            if ($part instanceof Identifier) {
                $value = '%' . $part->getIdentifier() . '%';

                $subQuery = $select->getAdapter()->select();
                foreach ($this->fields as $field) {
                    if ($part->isNegated()) {
                        $subQuery->where(sprintf('%s NOT LIKE ?', $field), $value);
                    } else {
                        $subQuery->orWhere(sprintf('%s LIKE ?', $field), $value);
                    }
                }
            } else if ($part instanceof Query) {
                $subQuery = $select->getAdapter()->select();
                $this->processQuery($subQuery, $part);
            }

            if ($subQuery) {
                // add assembled sub-query where condition to our main query
                $lastKeyword = array_pop($keywordStack);
                if (null !== $lastKeyword && $lastKeyword->getKeyword() === 'OR') {
                    $select->orWhere(implode(' ', $subQuery->getPart(\Zend_Db_Select::WHERE)));
                } else {
                    $select->where(implode(' ', $subQuery->getPart(\Zend_Db_Select::WHERE)));
                }
            }
        }
    }
}
