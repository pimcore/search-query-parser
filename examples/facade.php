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

$input = 'fo*o !bar "doe"';

dump($input);
dump(\SearchQueryParser\SearchQueryParser::parseQuery($input));
