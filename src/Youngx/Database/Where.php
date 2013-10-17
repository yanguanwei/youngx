<?php

namespace Youngx\Database;

class Where
{
    protected $where = array();
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __toString()
    {
        if ($this->where) {
            return '(1=1) ' . implode(' ', $this->where);
        }
        return '';
    }

    public function add($condition, $value = null)
    {
        $this->_add('AND', $condition, $value);
        return $this;
    }

    public function addOr($condition, $value = null)
    {
        $this->_add('OR', $condition, $value);
        return $this;
    }

    public function in($column, $value)
    {
        $this->addIn('AND', $column, $value);
        return $this;
    }

    public function inOr($column, $value)
    {
        $this->addIn('OR', $column, $value);
        return $this;
    }

    protected function addIn($op, $column, $value)
    {
        $this->_add($op, "{$column} IN ({Application::database()->quote($value)})");
        return $this;
    }

    /**
     * $this->add('AND', 'id in (?)', array(1, 2, 3))
     * $this->add('AND', "`title`='some title'");
     * $this->add('AND', "`title`=?", $title)
     * $this->add('AND', array(
     * '`id`=?' => $id,
     * '`title`<>:title',
     * "`visible`='1'"
     * 'OR' => array(
     *
     *  )
     * ))
     *
     * @param string $op
     *            AND | OR
     * @param $condition
     * @param string|array|null $value
     */
    protected function _add($op, $condition, $value = null)
    {
        $this->where[] = $this->_addLoop($op, $condition, $value);
    }

    protected function _addLoop($op, $condition, $value = null)
    {
        if (empty($condition)) {
            return ;
        }

        if (is_array($condition)) {
            $c = array();
            foreach($condition as $k => $v)
                if (is_int($k)) {
                    if (is_array($v)) {
                        $c[] = $this->_addLoop('AND', $v);
                    } else {
                        $c[] = "AND {$v}";
                    }
                } else if (strtoupper($k) === 'OR') {
                    $c[] = $this->_addLoop($k, $v);
                } else {
                    $c[] = "AND " . $this->connection->quoteInto($k, $v);
                }
            $s = "(1=1 " . implode(' ', $c) . ")";
        } else if ($value === null) {
            $s = $condition;
        } else {
            $s = $this->connection->quoteInto($condition, $value);
        }
        return "{$op} ({$s})";
    }
}
