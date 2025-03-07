<?php

/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2019/2/23
 * Time: 9:28
 */

namespace Zxin\Phinx\Schema\Definition;

class TableDefinition
{
    protected $options = [
        'id' => true,
        'signed' => false,
        'collation' => 'utf8mb4_general_ci',
    ];

    /**
     * 自定义主键名称 / false=关闭主键 / true=生成主键
     * @param bool|string $value
     * @return $this
     */
    public function id($value): TableDefinition
    {
        $this->options['id'] = $value;
        return $this;
    }

    /**
     * 自定义主键字段
     * @param array|string $value
     * @return $this
     */
    public function primaryKey($value): TableDefinition
    {
        $this->options['primary_key'] = $value;
        return $this;
    }

    /**
     * 设置主键字段为 UNSIGNED
     * @param bool $enable
     * @return $this
     */
    public function unsigned(bool $enable = true): TableDefinition
    {
        $this->options['signed'] = !$enable;
        return $this;
    }

    /**
     * 定义表注释
     * @param $text
     * @return $this
     */
    public function comment(string $text): TableDefinition
    {
        $this->options['comment'] = $text;
        return $this;
    }

    /**
     * 定义表排序规则
     * @param $value
     * @return $this
     */
    public function collation(string $value): TableDefinition
    {
        $this->options['collation'] = $value;
        return $this;
    }

    /**
     * 获取列定义
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
