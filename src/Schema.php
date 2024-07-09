<?php
declare(strict_types=1);

namespace Zxin\Phinx\Schema;

use Closure;
use Zxin\Phinx\Schema\Definition\TableDefinition;
use Phinx\Db\Table;
use Phinx\Migration\AbstractMigration;
use RuntimeException;

/**
 * Class Schema
 * @package Zxin\Phinx\Schema
 * @method static void create(string $tableName, Closure $closure, AbstractMigration $migration = null)
 * @method static void update(string $tableName, Closure $closure, AbstractMigration $migration = null)
 * @method static void save(string $tableName, Closure $closure, AbstractMigration $migration = null)
 */
class Schema
{
    /**
     * @var AbstractMigration
     */
    protected static $migration;
    protected static $compatibleVersion;

    protected $tableName;

    /**
     * @var Blueprint
     */
    protected $blueprint;

    /**
     * @var Table
     */
    protected $table;

    protected function __construct(string $tableName, AbstractMigration $migration = null)
    {
        $this->tableName    = $tableName;
        $this->table        = ($migration ?? self::$migration)->table($this->tableName);
        $this->blueprint    = new Blueprint($this, self::$compatibleVersion);
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public static function cxt(AbstractMigration $migration, Closure $closure, int $compatibleVersion = COMPATIBLE_VERSION_DEFAULT): void
    {
        $prevAM          = self::$migration;
        $prevCV          = self::$compatibleVersion;
        self::$migration = $migration;
        self::$compatibleVersion = $compatibleVersion;
        $closure();
        self::$migration = $prevAM;
        self::$compatibleVersion = $prevCV;
    }

    /**
     * @return AbstractMigration
     */
    public static function getMigration(): AbstractMigration
    {
        return self::$migration;
    }

    public static function __callStatic($name, $arguments)
    {
        $method = ['create', 'update', 'save'];
        if (!in_array($name, $method)) {
            throw new RuntimeException('Call to undefined method ' . static::class . '::' . $name . '()');
        }

        /** @var string $tableName */
        /** @var Closure $closure */
        /** @var AbstractMigration $migration */
        [$tableName, $closure, $migration] = array_pad($arguments, 3, null);

        $schema = new static($tableName, $migration);

        $closure($schema->blueprint, $schema);

        $schema->table->getTable()->setOptions($schema->blueprint->mergeTableOptions());

        if (empty($schema->blueprint->getColumns()) && empty($schema->blueprint->getIndexs())) {
            if ($name === 'create') {
                throw new RuntimeException('blueprint is empty by create: ' . $tableName);
            }
        }

        foreach ($schema->blueprint->getColumns() as $column) {
            if ($column->isChange()) {
                if ($name !== 'save') {
                    throw new RuntimeException('cannot change columns by create: ' . $column->getName());
                }
                $schema->table->changeColumn($column->getName(), $column->getColumn());
            } else {
                $schema->table->addColumn($column->getColumn());
            }
        }

        foreach ($schema->blueprint->getIndexs() as $index) {
            $schema->table->addIndex($index->getField(), $index->getOptions());
        }

        if ('create' === $name && self::getMigration()->isMigratingUp() && $schema->table->exists()) {
            // 触发警告
            trigger_error("table is exists: {$schema->table->getName()}, cannot be created", E_USER_WARNING);
            return;
        }
        $schema->table->{$name}();
    }

    public static function getCompatibleVersion(): int
    {
        return self::$compatibleVersion;
    }
}
