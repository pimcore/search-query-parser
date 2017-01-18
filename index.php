<?php

require_once __DIR__ . '/vendor/autoload.php';

$input = '!@mathias AND 1212 AND !foo OR (!(amya AND 12) blah) OR blubb';
$input = 'mathias !(@5020)';

dump($input);

$lexer = new \Query\Lexer();
$tokens = $lexer->lex($input);

dump($tokens);

$parser = new \Query\Parser($tokens);
$query = $parser->parse();
dump($query);

$db = new Zend_Db_Adapter_Pdo_Sqlite(array(
    'dbname' => ':memory:'
));

$select = $db
    ->select()
    ->from('foo');


$queryBuilder = new \Query\QueryBuilder([
    'foo',
    'bar'
]);
$select = $queryBuilder->processQuery($select, $query);

dump($select->__toString());

