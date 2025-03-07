<?php

namespace Zxin\Tests;

use PHPUnit\Framework\TestCase;
use ValueError;
use Zxin\Phinx\Schema\Definition\IndexDefinition;

class IndexDefinitionTest extends TestCase
{
    public function testOrder(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('order value can only be DESC or ASC');

        $inde = new IndexDefinition('index');
        $inde->order([
            'index' => 'ABC',
        ]);
    }
}
