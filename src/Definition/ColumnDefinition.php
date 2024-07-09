<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/1/28
 * Time: 17:46
 */

namespace Zxin\Phinx\Schema\Definition;

use Phinx\Db\Adapter\AdapterInterface as Adapter;
use Phinx\Db\Adapter\AdapterWrapper;
use Phinx\Db\Table\Column;
use Phinx\Util\Literal;
use Zxin\Phinx\Schema\Schema;
use function array_pad;
use function is_array;
use function method_exists;
use function Zxin\Phinx\Schema\to_snake_case;
use const Zxin\Phinx\Schema\COMPATIBLE_VERSION_DEFAULT;
use const Zxin\Phinx\Schema\COMPATIBLE_VERSION_OLD;

/**
 * 字段构造增强
 * Class ColumnDefinition
 * @method static ColumnDefinition column(string $type, string $name)
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
 * @method static ColumnDefinition float(string $name, int $precision = null, int $scale = null)
 * @method static ColumnDefinition double(string $name, int $precision = null, int $scale = null)
 * @method static ColumnDefinition decimal(string $name, int $precision = null, int $scale = null)
 *
 * @method static ColumnDefinition lockVersion() lockVersion
 * @method static ColumnDefinition createTime() createTime
 * @method static ColumnDefinition updateTime() updateTime
 * @method static ColumnDefinition deleteTime() deleteTime
 * @method static ColumnDefinition createdAt(?string $type = null, ?$limit = null) createdAt
 * @method static ColumnDefinition updatedAt(?string $type = null, ?$limit = null) updatedAt
 * @method static ColumnDefinition deletedAt(?string $type = null, ?$limit = null) deletedAt
 * @method static ColumnDefinition createdBy(?string $type = null, ?$limit = null) createdBy
 * @method static ColumnDefinition updatedBy(?string $type = null, ?$limit = null) updatedBy
 * @method static ColumnDefinition deletedBy(?string $type = null, ?$limit = null) deletedBy
 * @method static ColumnDefinition uuid() uuid
 * @method static ColumnDefinition status() status
 * @method static ColumnDefinition genre() genre
 * @method static ColumnDefinition remark() remark
 */
class ColumnDefinition
{
    const COMMENTS = [
        'createTime' => '创建时间',
        'updateTime' => '更新时间',
        'deleteTime' => '删除时间',
        'createBy'   => '创建者',
        'updateBy'   => '更新者',
        'creatorUid' => '创建者',
        'editorUid'  => '编辑者',
        'status'     => '状态',
        'genre'      => '类型',
        'remark'     => '备注',
    ];

    const MAPPING = [
        'bigInteger'   => [Adapter::PHINX_TYPE_BIG_INTEGER, null], // INT_REGULAR
        'integer'      => [Adapter::PHINX_TYPE_INTEGER, null], // INT_REGULAR
        'mediumInteger' => [Adapter::PHINX_TYPE_INTEGER, 16777215], // INT_MEDIUM
        'smallInteger' => [Adapter::PHINX_TYPE_SMALL_INTEGER, null], // INT_SMALL
        'tinyInteger'  => [Adapter::PHINX_TYPE_TINY_INTEGER, NULL], // INT_TINY

        'unsignedBigInteger'    => [Adapter::PHINX_TYPE_BIG_INTEGER, null], // INT_REGULAR
        'unsignedInteger'       => [Adapter::PHINX_TYPE_INTEGER, null], // INT_REGULAR
        'unsignedMediumInteger' => [Adapter::PHINX_TYPE_INTEGER, 16777215], // INT_MEDIUM
        'unsignedSmallInteger'  => [Adapter::PHINX_TYPE_SMALL_INTEGER, null], // INT_SMALL
        'unsignedTinyInteger'   => [Adapter::PHINX_TYPE_TINY_INTEGER, null], // INT_TINY

        'string' => [Adapter::PHINX_TYPE_STRING, 255], // TEXT_TINY
        'char'   => [Adapter::PHINX_TYPE_CHAR, null],
        'text'   => [Adapter::PHINX_TYPE_TEXT, null],

        'bit'       => [Adapter::PHINX_TYPE_BIT, null],
        'binary'    => [Adapter::PHINX_TYPE_BINARY, null],
        'varbinary' => [Adapter::PHINX_TYPE_VARBINARY, null],
        'blob'      => [Adapter::PHINX_TYPE_BLOB, null],

        'json'   => [Adapter::PHINX_TYPE_JSON, null],

        'float'   => [Adapter::PHINX_TYPE_FLOAT, null],
        'double'  => [Adapter::PHINX_TYPE_DOUBLE, null],
        'decimal' => [Adapter::PHINX_TYPE_DECIMAL, null],

        'lockVersion' => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'createTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updateTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'deleteTime'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        // ===> 弃用开始
        'createBy'    => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updateBy'    => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'creatorUid'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'editorUid'   => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        // <=== 弃用结束
        'status'      => [Adapter::PHINX_TYPE_INTEGER, 255],
        'genre'       => [Adapter::PHINX_TYPE_INTEGER, 255],
        'uuid'        => [Adapter::PHINX_TYPE_STRING, 36],
        'remark'      => [Adapter::PHINX_TYPE_STRING, 255],

        'createdAt'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updatedAt'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'deletedAt'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'createdBy'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'updatedBy'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
        'deletedBy'  => [Adapter::PHINX_TYPE_INTEGER, 4294967295],
    ];

    protected $change = false;

    /** @var Column */
    protected $column;

    protected $generatedExpression = null;

    protected function __construct(Column $column)
    {
        $this->column = $column;
    }

    public static function __callStatic($name, $arguments): ColumnDefinition
    {
        // 静态调用依赖上下文传输兼容级别
        return self::make($name, $arguments, Schema::getCompatibleVersion());
    }

    public static function make($callName, $arguments, int $compatibleVersion = COMPATIBLE_VERSION_DEFAULT): ColumnDefinition
    {
        if ($callName === 'column') {
            $type = $arguments[0];
            $name = $arguments[1];
            $limit = null;
            $arg1 = null;
        } else {
            [$name, $arg1] = array_pad($arguments, 2, null);
            /**
             * $name == null : field
             * $name <> null : type
             */
            $name = $name ?? to_snake_case($callName);
            [$type, $limit] = self::MAPPING[$callName];
        }


        $column = new Column();
        $column->setName($name);
        $column->setType($type);

        // 默认设置
        if (null !== $limit) {
            $column->setLimit($limit);
        }
        $column->setNull(false);

        // 按需设置
        switch ($callName) {
            case 'bigInteger':
            case 'integer':
            case 'mediumInteger':
            case 'smallInteger':
            case 'tinyInteger':
                if ($compatibleVersion === COMPATIBLE_VERSION_OLD) {
                    $column->setDefault(0);
                }
                break;
            case 'unsignedBigInteger':
            case 'unsignedInteger':
            case 'unsignedMediumInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
            case 'status':
            case 'genre':
                $column->setSigned(false);
                if ($compatibleVersion === COMPATIBLE_VERSION_OLD) {
                    $column->setDefault(0);
                }
                break;
            case 'string':
                $column->setLimit($arg1);
                if ($compatibleVersion === COMPATIBLE_VERSION_OLD) {
                    $column->setDefault('');
                }
                break;
            case 'char':
            case 'bit':
            case 'binary':
            case 'varbinary':
                $column->setLimit($arg1);
                break;
            case 'float':
            case 'double':
            case 'decimal':
                if (isset($arguments[1]) && is_numeric($arguments[1])) {
                    $column->setPrecision($arguments[1]);
                }
                if (isset($arguments[2]) && is_numeric($arguments[2])) {
                    $column->setScale($arguments[2]);
                }
                if ($compatibleVersion === COMPATIBLE_VERSION_OLD) {
                    $column->setDefault(0);
                }
                break;
            case 'lockVersion':
            case 'createTime':
            case 'updateTime':
            case 'deleteTime':
                // ===> 弃用开始
            case 'creatorUid':
            case 'editorUid':
            case 'createBy':
            case 'updateBy':
                // <=== 弃用结束
                $column->setSigned(false);
                $column->setDefault(0);
                $column->setComment(self::COMMENTS[$callName] ?? null);
                break;
            case 'createdAt':
            case 'updatedAt':
            case 'deletedAt':
            case 'createdBy':
            case 'updatedBy':
            case 'deletedBy':
                $overlayType = $arguments[0] ?? null;
                if ($overlayType && is_string($overlayType)) {
                    $column->setType($overlayType);
                }
                $overlayLimit = $arguments[1] ?? null;
                if (null !== $overlayLimit) {
                    $column->setLimit($overlayLimit);
                }
                $column->setSigned(false);
                if ($compatibleVersion === COMPATIBLE_VERSION_OLD) {
                    $column->setDefault(0);
                } else {
                    if (!('createdAt' === $callName || 'createdBy' === $callName)) {
                        $column->setDefault(0);
                    }
                }
                $column->setComment(self::COMMENTS[$callName] ?? null);
                break;
            case 'uuid':
                $column->setCollation('ascii');
                $column->setCollation('ascii_general_ci');
                break;
            case 'remark':
                $column->setDefault('');
                break;
        }

        return new ColumnDefinition($column);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->column->getName();
    }

    /**
     * @param string $expression
     * @param bool   $stored
     * @return ColumnDefinition
     */
    public function generated(string $expression, bool $stored = false): ColumnDefinition
    {
        $this->generatedExpression = [
            $expression,
            $stored,
        ];
        return $this;
    }

    protected function isGenerated(): bool
    {
        return $this->generatedExpression !== null;
    }

    /**
     * 将此字段放置在其它字段 "之后" (MySQL)
     * @param string $columnName
     * @return $this
     */
    public function after(string $columnName): ColumnDefinition
    {
        $this->column->setAfter($columnName);
        return $this;
    }

    /**
     * 将 INTEGER 类型的字段设置为自动递增的主键
     * @param bool $enable
     * @return $this
     */
    public function autoIncrement(bool $enable = true): ColumnDefinition
    {
        $this->column->setIdentity($enable);
        return $this;
    }

    /**
     * 指定一个字符集 (MySQL)
     * @param string $collation eg: utf8mb4
     * @return $this
     */
    public function charset(string $collation): ColumnDefinition
    {
        $this->column->setCollation($collation);
        return $this;
    }

    /**
     * 指定列的排序规则 (MySQL)
     * @param string $encoding  eg: utf8mb4
     * @param string $collation  eg: utf8mb4_general_ci
     * @return $this
     */
    public function collation(string $encoding, string $collation): ColumnDefinition
    {
        $this->column->setEncoding($encoding);
        $this->column->setCollation($collation);
        return $this;
    }

    public function ccAscii(): ColumnDefinition
    {
        $this->asciiCharacter();
        return $this;
    }

    public function asciiCharacter(string $collation = 'ascii_general_ci'): ColumnDefinition
    {
        $this->collation('ascii', $collation);
        return $this;
    }

    /**
     * 为字段增加注释
     * @param string $comment
     * @return $this
     */
    public function comment(string $comment): ColumnDefinition
    {
        $this->column->setComment($comment);
        return $this;
    }

    /**
     * 为字段指定 "默认" 值
     * @param string|int|Literal|null $default
     * @return $this
     */
    public function default($default): ColumnDefinition
    {
        $this->column->setDefault($default);
        return $this;
    }

    /**
     * 此字段允许写入 NULL 值
     * @return $this
     */
    public function nullable(bool $nullable): ColumnDefinition
    {
        $this->column->setNull($nullable);
        return $this;
    }

    /**
     * 设置 INTEGER 类型的字段为 UNSIGNED (MySQL)
     * @param bool $enable
     * @return $this
     */
    public function unsigned(bool $enable = true): ColumnDefinition
    {
        $this->column->setSigned(!$enable);
        return $this;
    }

    public function limit(int $limit): ColumnDefinition
    {
        $this->column->setLimit($limit);
        return $this;
    }

    public function identity(bool $enable): ColumnDefinition
    {
        $this->column->setIdentity($enable);
        return $this;
    }

    /**
     * @return $this
     */
    public function change(): ColumnDefinition
    {
        $this->change = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isChange(): bool
    {
        return $this->change;
    }

    /**
     * 获取列
     * @return Column
     */
    public function getColumn(): Column
    {
        $this->buildGenerated();
        return $this->column;
    }

    private function buildGenerated()
    {
        if (!$this->isGenerated()) {
            return;
        }
        [$expression, $stored] = $this->generatedExpression;
        $originalType = (string) $this->column->getType();
        // 寻找适配器
        $adapter = Schema::getMigration()->getAdapter();
        while ($adapter instanceof AdapterWrapper && method_exists($adapter, 'getAdapter')) {
            $adapter = $adapter->getAdapter();
        }
        // 获取可无符号列
        $getSignedColumnTypes = function () {
            return $this->signedColumnTypes ?? null;
        };
        $signedColumnTypes = $getSignedColumnTypes->call($adapter);
        // 获取列类型
        $type = $adapter->getSqlType($originalType, $this->column->getLimit());
        if (isset($type['limit'])) {
            $typeDef = "{$type['name']}({$type['limit']})";
        } else {
            $typeDef = $type['name'];
        }
        // 构建表达式
        $stored = $stored ? ' STORED' : ' VIRTUAL';
        $isUnsigned = !$this->column->isSigned()
            && is_array($signedColumnTypes)
            && isset($signedColumnTypes[$originalType]);
        $unsigned = $isUnsigned ? ' unsigned' : '';
        // 重置为有符号
        $this->column->setSigned(true);
        $this->column->setType(Literal::from("{$typeDef}{$unsigned} AS ($expression){$stored}"));
        $this->column->setDefault(null);
    }
}
