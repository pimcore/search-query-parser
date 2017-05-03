<?php

namespace SearchQueryParser\Test;

use PHPUnit\Framework\TestCase;
use SearchQueryParser\Part\Query;
use SearchQueryParser\QueryBuilder\ZendDbSelect;
use SearchQueryParser\SearchQueryParser;

/**
 * Simple end-to-end test testing SQL output from given input
 */
class ZendDbEndToEndTest extends TestCase
{
    /**
     * @var \Zend_Db_Adapter_Pdo_Sqlite
     */
    protected $db;

    protected function setUp()
    {
        parent::setUp();

        $this->db = new \Zend_Db_Adapter_Pdo_Sqlite([
            'dbname' => ':memory:'
        ]);
    }

    /**
     * @return \Zend_Db_Select
     */
    protected function buildSelect()
    {
        // dummy query
        return $this->db
            ->select()
            ->from('foo');
    }

    /**
     * @param string $input
     * @param array $fields
     * @param int|null $expectedParts
     *
     * @return string
     */
    protected function getWhere($input, array $fields = [], $expectedParts = null)
    {
        $queryBuilder = new ZendDbSelect($fields);

        $query = SearchQueryParser::parseQuery($input);
        $this->assertInstanceOf(Query::class, $query);

        $select = $this->buildSelect();

        $queryBuilder->processQuery($select, $query);

        $where = $select->getPart(\Zend_Db_Select::WHERE);

        if (null !== $expectedParts) {
            $this->assertCount(
                $expectedParts, $where,
                'Returned where query is expected to contain ' . $expectedParts . ' parts'
            );
        }

        $where = implode(' ', $where);

        return $where;
    }

    public function testFuzzyTerm()
    {
        $where = $this->getWhere('foo', ['field1'], 1);

        $this->assertEquals(
            $where,
            "((field1 LIKE '%foo%'))"
        );
    }

    public function testNegatedFuzzyTerm()
    {
        $where = $this->getWhere('!foo', ['field1'], 1);

        $this->assertEquals(
            $where,
            "((field1 IS NULL OR field1 NOT LIKE '%foo%'))"
        );
    }

    public function testQuotedTerm()
    {
        $where = $this->getWhere('"foo"', ['field1'], 1);

        $this->assertEquals(
            $where,
            "((field1 = 'foo'))"
        );
    }

    public function testNegatedQuotedTerm()
    {
        $where = $this->getWhere('!"foo"', ['field1'], 1);

        $this->assertEquals(
            $where,
            "((field1 IS NULL OR field1 != 'foo'))"
        );
    }

    public function testSingleFieldWithMultipleTerms()
    {
        $where = $this->getWhere('foo AND bar', ['field1'], 2);

        $this->assertEquals(
            $where,
            "((field1 LIKE '%foo%')) AND ((field1 LIKE '%bar%'))"
        );
    }

    public function testMultipleFieldsWithMultipleTerms()
    {
        $where = $this->getWhere('foo AND bar', ['field1', 'field2'], 2);

        $this->assertEquals(
            "((field1 LIKE '%foo%') OR (field2 LIKE '%foo%')) AND ((field1 LIKE '%bar%') OR (field2 LIKE '%bar%'))",
            $where
        );
    }

    public function testNegatedQuery()
    {
        $where = $this->getWhere(
            '!(foo AND bar)',
            ['field1'],
            1
        );

        $this->assertEquals(
            "(NOT(((field1 LIKE '%foo%')) AND ((field1 LIKE '%bar%'))))",
            $where
        );
    }

    // TODO more granular tests
    public function testComplexQuery()
    {
        $where = $this->getWhere(
            'doe AND "1212" AND !foo OR (!("amya" AND 12) blah) OR baz',
            ['field1', 'field2'],
            5
        );

        $this->assertEquals(
            "((field1 LIKE '%doe%') OR (field2 LIKE '%doe%')) AND ((field1 = '1212') OR (field2 = '1212')) AND ((field1 IS NULL OR field1 NOT LIKE '%foo%') AND (field2 IS NULL OR field2 NOT LIKE '%foo%')) OR ((NOT(((field1 = 'amya') OR (field2 = 'amya')) AND ((field1 LIKE '%12%') OR (field2 LIKE '%12%')))) AND ((field1 LIKE '%blah%') OR (field2 LIKE '%blah%'))) OR ((field1 LIKE '%baz%') OR (field2 LIKE '%baz%'))",
            $where
        );
    }
}
