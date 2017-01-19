<?php

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'foo !bar "doe"';

dump($input);
dump(\SearchQueryParser\SearchQueryParser::parseQuery($input));
