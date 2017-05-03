<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$input = 'foo !bar "doe"';

dump($input);
dump(\SearchQueryParser\SearchQueryParser::parseQuery($input));
