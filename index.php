<?php

require_once __DIR__ . '/vendor/autoload.php';

$input = 'mathias AND 1212 AND !foo OR ((amya AND 12) blah) OR blubb';

dump($input);

$lexer = new \Query\Lexer();
$tokens = $lexer->lex($input);

dump($tokens);

$parser = new \Query\Parser($tokens);
dump($parser->parse());
