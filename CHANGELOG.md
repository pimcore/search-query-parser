Changelog
=========

2026.4.0
--------

* Require PHP >= 8.3 (was >= 8.0), the lowest version on which the development dependencies (PHPUnit 12/13 and
  symfony/var-dumper 7.4) can be installed. The other release lines (1.x, 2.x, 2026.2 and 2026.3) are unchanged, so
  projects on PHP 8.0 to 8.2 can stay on them.

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

