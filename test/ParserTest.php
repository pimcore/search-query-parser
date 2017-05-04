<?php

declare(strict_types=1);

namespace SearchQueryParser\Test;

use PHPUnit\Framework\TestCase;
use SearchQueryParser\Lexer;
use SearchQueryParser\Parser;
use SearchQueryParser\Part\Keyword;
use SearchQueryParser\Part\PartInterface;
use SearchQueryParser\Part\Query;
use SearchQueryParser\Part\Term;

class ParserTest extends TestCase
{
    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var Parser
     */
    protected $parser;

    protected function setUp()
    {
        $this->lexer  = new Lexer();
        $this->parser = new Parser();
    }

    /**
     * @param $input
     *
     * @return \SearchQueryParser\Part\Query
     */
    protected function getQuery($input)
    {
        $tokens = $this->lexer->lex($input);
        $query  = $this->parser->parse($tokens);

        $this->assertInstanceOf(Query::class, $query);

        return $query;
    }

    public function testSimpleTerm()
    {
        $query = $this->getQuery('foo');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            'foo', false, false
        );
    }

    public function testNegatedTerm()
    {
        $query = $this->getQuery('!foo');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            'foo', false, true
        );
    }

    public function testFuzzyTerm()
    {
        $query = $this->getQuery('*foo*');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', true, false
        );
    }

    public function testFuzzyTermAtStart()
    {
        $query = $this->getQuery('*foo');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo', true, false
        );
    }

    public function testFuzzyTermAtEnd()
    {
        $query = $this->getQuery('foo*');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            'foo*', true, false
        );
    }

    public function testFuzzyTermInBetween()
    {
        $query = $this->getQuery('fo*o');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            'fo*o', true, false
        );
    }

    public function testQuotedTermIsNotFuzzy()
    {
        $query = $this->getQuery('"*foo*"');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', false, false
        );
    }

    public function testSingleQuotedTermIsNotFuzzy()
    {
        $query = $this->getQuery("'*foo*'");

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', false, false
        );
    }

    public function testNegatedFuzzyTerm()
    {
        $query = $this->getQuery('!*foo*');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', true, true
        );
    }

    public function testNegatedQuotedTermIsNotFuzzy()
    {
        $query = $this->getQuery('!"*foo*"');

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', false, true
        );
    }

    public function testNegatedSingleQuotedTermIsNotFuzzy()
    {
        $query = $this->getQuery("!'*foo*'");

        $this->assertCount(1, $query->getParts());
        $this->assertTerm(
            $query->getPart(0),
            '*foo*', false, true
        );
    }

    public function testTermsAreAndCombinedByDefault()
    {
        $query = $this->getQuery('foo bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'AND');
        $this->assertTerm($query->getPart(2), 'bar');
    }

    public function testTermsAreAndCombinedByDefaultWhenFuzzy()
    {
        $query = $this->getQuery('foo* *bar*');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo*', true);
        $this->assertKeyword($query->getPart(1), 'AND');
        $this->assertTerm($query->getPart(2), '*bar*', true);
    }

    public function testTermsAreAndCombinedByDefaultWhenNegated()
    {
        $query = $this->getQuery('foo !bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'AND');
        $this->assertTerm($query->getPart(2), 'bar', false, true);
    }

    public function testTermsAreAndCombinedExplicitely()
    {
        $query = $this->getQuery('foo AND bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'AND');
        $this->assertTerm($query->getPart(2), 'bar');
    }

    public function testTermsAreAndCombinedExplicitelyWhenNegated()
    {
        $query = $this->getQuery('foo AND !bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'AND');
        $this->assertTerm($query->getPart(2), 'bar', false, true);
    }

    public function testTermsAreOrCombinedExplicitely()
    {
        $query = $this->getQuery('foo OR bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'OR');
        $this->assertTerm($query->getPart(2), 'bar');
    }

    public function testTermsAreOrCombinedExplicitelyWhenNegated()
    {
        $query = $this->getQuery('foo OR !bar');

        $this->assertCount(3, $query->getParts());

        $this->assertTerm($query->getPart(0), 'foo');
        $this->assertKeyword($query->getPart(1), 'OR');
        $this->assertTerm($query->getPart(2), 'bar', false, true);
    }

    /**
     * @param PartInterface|Term $term
     * @param string $value
     * @param bool $fuzzy
     * @param bool $negated
     */
    protected function assertTerm($term, $value, $fuzzy = false, $negated = false)
    {
        $this->assertInstanceOf(Term::class, $term);
        $this->assertEquals($value, $term->getTerm());
        $this->assertEquals($fuzzy, $term->isFuzzy());
        $this->assertEquals($negated, $term->isNegated());
    }

    /**
     * @param PartInterface|Keyword $keyword
     * @param string $value
     */
    protected function assertKeyword($keyword, $value)
    {
        $this->assertInstanceOf(Keyword::class, $keyword);
        $this->assertEquals($value, $keyword->getKeyword());
    }
}
