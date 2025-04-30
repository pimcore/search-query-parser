<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'doe AND "1212" AND !foo OR (!("amya" AND 12) blah) OR baz'; // complex query
$input = '*john* !5020 AND "foob*ar"'; // LIKE %john% AND != 5020 AND = foob*ar
dump($input);

$lexer = new \SearchQueryParser\Lexer();
$tokens = $lexer->lex($input);

// contains array of extracted tokens
dump($tokens);

$parser = new \SearchQueryParser\Parser();
$query = $parser->parse($tokens);

// contains the abstract Query object
dump($query);

// use the Zend_Db query builder to transform the Query into conditions
$db = new Zend_Db_Adapter_Pdo_Sqlite([
    'dbname' => ':memory:'
]);

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
