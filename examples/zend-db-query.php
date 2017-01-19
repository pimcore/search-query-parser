<?php

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'doe AND "1212" AND !foo OR (!("amya" AND 12) blah) OR baz';
//$input = 'doe !"john"';
dump($input);

$lexer = new \SearchQueryParser\Lexer();
$tokens = $lexer->lex($input);

dump($tokens);

$parser = new \SearchQueryParser\Parser();
$query = $parser->parse($tokens);

dump($query);

$db = new Zend_Db_Adapter_Pdo_Sqlite(array(
    'dbname' => ':memory:'
));

$select = $db
    ->select()
    ->from('foo');

$queryBuilder = new \SearchQueryParser\QueryBuilder\ZendDbSelect([
    'foo',
    'bar'
]);

$select = $queryBuilder->processQuery($select, $query);
dump($select->__toString());
