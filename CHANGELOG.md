Changelog
=========

2.0.0
-----

* To support DBAL v4, SearchQueryParser\QueryBuilder\Doctrine::processQuery() requires a DBAL\Connection as third parameter

1.2.3
-----

* Removed restriction to `@` in terms (denoted strict search in earlier versions)

1.0.0
-----

* Require PHP >= 7.0
* Changed fuzzy search syntax from `foo` to `*foo*`, defaulting to non-fuzzy
* Changed lexer output to `Token` value objects

