<?php

namespace Zxin\Tests\Migrations;

use Phinx\Migration\AbstractMigration;
use Zxin\Phinx\Schema\Blueprint;
use Zxin\Phinx\Schema\Schema;

class RemoveColumn extends AbstractMigration
{
    public function up(): void
    {
        Schema::cxt($this, function (): void {
            Schema::save('system', function (Blueprint $blueprint): void {
                $blueprint->table->removeColumn('text');
                $blueprint->table->removeColumn('json');
            });
        });
    }

    public function down(): void
    {
        Schema::cxt($this, function (): void {
            Schema::update('system', function (Blueprint $blueprint): void {
                $blueprint->json('json')->after('char');
                $blueprint->text('text')->after('char');
            });
        });
    }
}
