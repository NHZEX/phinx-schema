<?php
declare(strict_types=1);

namespace Zxin\Phinx\Schema;

use Phinx\Db\Table;
use RuntimeException;
use Zxin\Phinx\Schema\Definition\ColumnDefinition;
use Zxin\Phinx\Schema\Definition\IndexDefinition;
use Zxin\Phinx\Schema\Definition\TableDefinition;

/**
 * Class Blueprint
 * @package Zxin\Phinx\Schema
 *
 * @property-write string|bool  $id         自定义主键名称 / false=关闭主键 / true=生成主键
 * @property-write string|array $primaryKey 自定义主键字段
 * @property-write bool         $unsigned   设置主键字段为 UNSIGNED
 * @property-write string       $comment    定义表注释
 * @property-write string       $collation  定义表排序规则
 *
 * @method ColumnDefinition column(string $type, string $name)
 *
 * @method static ColumnDefinition tinyInteger(string $name) 相当于 tinyint
 * @method static ColumnDefinition unsignedTinyInteger(string $name) 相当于 unsigned tinyint
 * @method static ColumnDefinition smallInteger(string $name) 相当于 smallint
 * @method static ColumnDefinition unsignedSmallInteger(string $name) 相当于 unsigned smallint
 * @method static ColumnDefinition mediumInteger(string $name) 相当于 mediumint
 * @method static ColumnDefinition unsignedMediumInteger(string $name) 相当于 unsigned mediumint
 * @method static ColumnDefinition integer(string $name) 相当于 integer
 * @method static ColumnDefinition unsignedInteger(string $name) 相当于 unsigned integer
 * @method static ColumnDefinition bigInteger(string $name) 相当于 bigint
 * @method static ColumnDefinition unsignedBigInteger(string $name) 相当于 unsigned bigint
 * @method static ColumnDefinition string(string $name, int $limit) 相当于带长度的 varchar
 * @method static ColumnDefinition char(string $name, int $limit) 相当于带有长度的 char
 * @method static ColumnDefinition text(string $name) 相当于 text
 * @method static ColumnDefinition bit(string $name, int $limit)
 * @method static ColumnDefinition binary(string $name, int $limit)
 * @method static ColumnDefinition varbinary(string $name, int $limit)
 * @method static ColumnDefinition blob(string $name) 相当于 blob
 * @method static ColumnDefinition json(string $name) 相当于 json
 * @method ColumnDefinition float(string $name, int $precision = null, int $scale = null)
 * @method ColumnDefinition double(string $name, int $precision = null, int $scale = null)
 * @method ColumnDefinition decimal(string $name, int $precision = null, int $scale = null)
 *
 * @method ColumnDefinition lockVersion() lockVersion
 * @method ColumnDefinition createTime() createTime
 * @method ColumnDefinition updateTime() updateTime
 * @method ColumnDefinition deleteTime() deleteTime
 * @method ColumnDefinition createBy() createBy
 * @method ColumnDefinition updateBy() updateBy
 * @method ColumnDefinition uuid() uuid
 * @method ColumnDefinition status() status
 * @method ColumnDefinition genre() genre
 * @method ColumnDefinition remark() remark
 */
class Blueprint
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var TableDefinition
     */
    protected $tableDefinition;

    /**
     * @var ColumnDefinition[]
     */
    protected $columns = [];

    /**
     * @var IndexDefinition[]
     */
    protected $indexs = [];
    /**
     * @var Table
     */
    public $table;

    public function __construct(Schema $schema)
    {
        $this->schema          = $schema;
        $this->table           = $schema->getTable();
        $this->tableDefinition = new TableDefinition();
    }

    /**
     * 获取表单定义对象
     * @return TableDefinition
     */
    public function getOptions(): TableDefinition
    {
        return $this->tableDefinition;
    }

    /**
     * 获取合并表定义选项
     * @return array
     */
    public function mergeTableOptions(): array
    {
        return array_merge($this->schema->getTable()->getOptions(), $this->tableDefinition->getOptions());
    }

    /**
     * 设置表单定义
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->tableDefinition->{$name}($value);
    }

    /**
     * @param string $callName
     * @param array  $arguments
     * @return ColumnDefinition
     */
    public function __call(string $callName, array $arguments): ColumnDefinition
    {
        $column          = ColumnDefinition::make($callName, $arguments);
        if (isset($this->columns[$column->getName()])) {
            throw new RuntimeException('duplicate definition column ' . $column->getName());
        }
        $this->columns[$column->getName()] = $column;
        return $column;
    }

    /**
     * 添加普通索引
     * @param string|string[] $field
     * @param string|null     $name
     * @return IndexDefinition
     */
    public function index($field, string $name = null): IndexDefinition
    {
        $index = new IndexDefinition($field);
        $name = $name ?? IndexDefinition::generateName($field);
        $index->name($name);
        if (isset($this->indexs[$name])) {
            throw new RuntimeException('duplicate definition index ' . $name);
        }
        $this->indexs[$name] = $index;
        return $index;
    }

    /**
     * 添加唯一索引
     * @param string|string[] $field
     * @return IndexDefinition
     */
    public function unique($field): IndexDefinition
    {
        $index = $this->index($field);
        $index->unique();
        return $index;
    }

    /**
     * @param ColumnDefinition $column
     * @return ColumnDefinition
     */
    public function addColumn(ColumnDefinition $column): ColumnDefinition
    {
        if (empty($column->getName())) {
            throw new RuntimeException('column name is empty');
        }
        if (isset($this->columns[$column->getName()])) {
            throw new RuntimeException('duplicate definition column ' . $column->getName());
        }
        $this->columns[$column->getName()] = $column;
        return $column;
    }

    /**
     * @return ColumnDefinition[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return IndexDefinition[]
     */
    public function getIndexs(): array
    {
        return $this->indexs;
    }
}
