<?php

declare(strict_types=1);

namespace SearchQueryParser\QueryBuilder;

use SearchQueryParser\Part\Keyword;
use SearchQueryParser\Part\Query;
use SearchQueryParser\Part\Term;

class ZendDbSelect
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $options = [
        'stripWildcards' => true
    ];

    /**
     * @param array $fields
     * @param array $options
     */
    public function __construct(array $fields = [], array $options = [])
    {
        $this->fields  = $fields;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param \Zend_Db_Select $select
     * @param Query $query
     */
    public function processQuery(\Zend_Db_Select $select, Query $query)
    {
        if (empty($this->fields)) {
            throw new \RuntimeException('Query can\'t be processed as no fields were configured');
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
            $subQuery        = null;
            $negatedSubQuery = false;

            if ($part instanceof Term) {
                $value = $part->getTerm();
                if ($part->isFuzzy()) {
                    $value = $this->buildFuzzyValue($value);
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
            } elseif ($part instanceof Query) {
                $subQuery = $select->getAdapter()->select();
                $this->processQuery($subQuery, $part);

                $negatedSubQuery = $part->isNegated();
            }

            if ($subQuery) {
                // add assembled sub-query where condition to our main query
                $lastKeyword = array_pop($keywordStack);

                $subWhere = implode(' ', $subQuery->getPart(\Zend_Db_Select::WHERE));

                if ($negatedSubQuery) {
                    $subWhere = 'NOT(' . $subWhere . ')';
                }

                if (null !== $lastKeyword && $lastKeyword->getKeyword() === 'OR') {
                    $select->orWhere($subWhere);
                } else {
                    $select->where($subWhere);
                }
            }
        }
    }

    /**
     * @param string $option
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    protected function getOption($option, $defaultValue = null)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return $defaultValue;
    }

    /**
     * @param Term $term
     * @param string $field
     *
     * @return string
     */
    protected function buildTermCondition(Term $term, string $field): string
    {
        $condition = null;
        if ($term->isFuzzy()) {
            if ($term->isNegated()) {
                $condition = '%1$s IS NULL OR %1$s NOT LIKE ?';
            } else {
                $condition = '%s LIKE ?';
            }
        } else {
            if ($term->isNegated()) {
                $condition = '%1$s IS NULL OR %1$s != ?';
            } else {
                $condition = '%s = ?';
            }
        }

        return sprintf($condition, $field);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function buildFuzzyValue(string $value): string
    {
        if ($this->getOption('stripWildcards', false)) {
            $value = str_replace(['%', '_'], '', $value);
        }

        $value = str_replace('*', '%', $value);

        return $value;
    }
}
