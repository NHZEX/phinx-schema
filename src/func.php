<?php

namespace Zxin\Phinx\Schema;

use function preg_replace;
use function strtolower;

const COMPATIBLE_VERSION_OLD     = 0;
const COMPATIBLE_VERSION_NEXT    = 1;
const COMPATIBLE_VERSION_DEFAULT = COMPATIBLE_VERSION_NEXT;

/**
 * @param string $input
 * @return string
 */
function to_snake_case(string $input): string
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
}