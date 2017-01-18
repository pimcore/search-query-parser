<?php

namespace Query;

use Query\Part\Keyword;
use Query\Part\Query;
use Query\Part\Term;

class QueryBuilder
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param \Zend_Db_Select $select
     * @param Query $query
     * @return \Zend_Db_Select
     */
    public function processQuery(\Zend_Db_Select $select, Query $query)
    {
        if (empty($this->fields)) {
            return $select;
        }

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

            if ($part instanceof Term) {
                $value = $part->getTerm();
                if ($part->isFuzzy()) {
                    $value = '%' . $value . '%';
                }

                $subQuery = $select->getAdapter()->select();
                foreach ($this->fields as $field) {
                    $condition = $this->buildTermCondition($part, $field);

                    if ($part->isNegated()) {
                        $subQuery->where($condition, $value);
                    } else {
                        $subQuery->orWhere($condition, $value);
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

        return $select;
    }

    /**
     * @param Term $term
     * @param string $field
     * @return string
     */
    protected function buildTermCondition(Term $term, $field)
    {
        $condition = null;
        if ($term->isFuzzy()) {
            if ($term->isNegated()) {
                $condition = '%s NOT LIKE ?';
            } else {
                $condition = '%s LIKE ?';
            }
        } else {
            if ($term->isNegated()) {
                $condition = '%s != ?';
            } else {
                $condition = '%s = ?';
            }
        }

        return sprintf($condition, $field);
    }
}
