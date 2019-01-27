# SearchQueryParser

[![Build Status](https://travis-ci.org/pimcore/search-query-parser.svg?branch=master)](https://travis-ci.org/pimcore/search-query-parser)

A basic search query parsing library which can be used to transform free-text searches 
into SQL (or other) queries. The analyzed search query can be used to build complex
(SQL) queries, searching for a term in multiple fields. Take a look at the 
[`Zend_Db` end to end test](test/ZendDbEndToEndTest.php#L71) an example or have a
look into [examples/](examples/) for working examples.

## Syntax 

Search terms can be AND/OR combined, negated, grouped with parentheses and modified to fuzzy exact search with the following syntax:

* `foo`
* `foo* AND bar`
* `*foo* OR *bar*`
* `*foo* OR !*bar*`
* `*foo* AND bar`
* `foo OR (bar AND baz)`

### Modifiers

Fuzzy search: `*<term>*`    
Negation: `!<term> !(<query>) !*<fuzzy-term>`

## Usage

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'doe AND 1212 AND !foo OR (!(am*ya AND 12) blah) OR baz'; // complex query
$input = '*john* !502* AND foobar'; // LIKE %john% AND NOT LIKE 502% AND = foobar

// tokenizes query string
$lexer = new \SearchQueryParser\Lexer();

// contains array of extracted tokens
$tokens = $lexer->lex($input);
dump($tokens);

// parses tokens into an abstract query
$parser = new \SearchQueryParser\Parser();
$query = $parser->parse($tokens);
dump($query);

// use the Zend_Db query builder to transform the Query into conditions
$db = new Zend_Db_Adapter_Pdo_Sqlite(array(
    'dbname' => ':memory:'
));

// dummy query
$select = $db
    ->select()
    ->from('foo');

// build conditions for every passed field
$queryBuilder = new \SearchQueryParser\QueryBuilder\ZendDbSelect([
    'foo',
    'bar'
]);

$queryBuilder->processQuery($select, $query);
dump($select->__toString());
```

### Using the facade

There's a facade class which can be called if you want to use the standard lexer/parser. 

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'foo !bar *doe*';

// returns an abstract query
dump(\SearchQueryParser\SearchQueryParser::parseQuery($input));
```

## Tests

There are a couple of simple end-to-end tests checking the output for a defined input. Just run `vendor/bin/phpunit` from
the project root.
