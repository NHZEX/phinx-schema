<?php

namespace Zxin\Phinx\Schema\Definition;

use ValueError;
use function strtoupper;

class IndexDefinition
{
    protected $options = [];

    /**
     * @param string|string[] $field
     * @return string
     */
    public static function generateName($field)
    {
        if (\is_array($field)) {
            $name = implode('_', $field);
        } else {
            $name = $field;
        }
        return $name;
    }

    /**
     * @var string|string[]|null
     */
    protected $field = null;

    /**
     * IndexDefinition constructor.
     * @param string|string[]|null $field
     */
    public function __construct($field = null)
    {
        $this->field = $field;
    }

    /**
     * 是否唯一
     * @param bool $value
     * @return $this
     */
    public function unique(bool $value = true): IndexDefinition
    {
        $this->options['unique'] = $value;
        return $this;
    }

    /**
     * 索引长度
     * @param int|array $value
     * @return $this
     */
    public function limit($value): IndexDefinition
    {
        $this->options['limit'] = $value;
        return $this;
    }

    /**
     * 索引名称
     * @param string $name
     * @return $this
     */
    public function name(string $name): IndexDefinition
    {
        $this->options['name'] = $name;
        return $this;
    }

    /**
     * 设置主键字段为 UNSIGNED
     * @param bool $enable
     * @return $this
     */
    public function unsigned(bool $enable = true): IndexDefinition
    {
        $this->options['signed'] = !$enable;
        return $this;
    }

    /**
     * 定义表注释
     * @param $text
     * @return $this
     */
    public function comment(string $text): IndexDefinition
    {
        $this->options['comment'] = $text;
        return $this;
    }

    /**
     * 设置 fulltext 索引 (mysql)
     * @param bool $enable
     * @return $this
     */
    public function fulltext(bool $enable = true): IndexDefinition
    {
        if (!$enable && isset($this->options['type']) && 'fulltext' === $this->options['type']) {
            unset($this->options['type']);
        } else {
            $this->options['type'] = 'fulltext';
        }
        return $this;
    }

    /**
     * 定义表排序规则
     * @param $value
     * @return $this
     */
    public function collation(string $value): IndexDefinition
    {
        $this->options['collation'] = $value;
        return $this;
    }

    /**
     * @param array $order
     * @return $this
     */
    public function order(array $order): IndexDefinition
    {
        foreach ($order as $value) {
            $value = strtoupper($value);
            if ($value !== 'DESC' && $value !== 'ASC') {
                throw new ValueError('order value can only be DESC or ASC');
            }
        }
        $this->options['order'] = $order;
        return $this;
    }

    /**
     * @return string|string[]|null
     */
    public function getField()
    {
        return $this->field;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
