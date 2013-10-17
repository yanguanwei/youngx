<?php

namespace Youngx\Database;

class Select
{
    private $sql = array(
        'from' => array(),
        'where' => null,
        'order' => array(),
        'limit' => array()
    );

    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __toString()
    {
        try {
            return (string) $this->toSQL();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getFields($tableAlias = null)
    {
        if (null === $tableAlias) {
            $fields = array();
            foreach ($this->sql['from'] as $tableAlias => $meta) {
                $fields[$tableAlias] = $meta['fields'];
            }
            return $fields;
        } else {
            return $this->sql['from'][$tableAlias]['fields'];
        }
    }

    public function addFields($tableAlias, $fields)
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        foreach($fields as $alias => $field) {
            $field = trim($field);
            if (is_int($alias)) {
                $alias = $field;
            }
            if (!in_array($field, $this->sql['from'][$tableAlias]['fields'])) {
                $this->sql['from'][$tableAlias]['fields'][$alias] = $field;
            }
        }

        return $this;
    }

    public function from($table, $fields = null)
    {
        $tableAlias = $this->addTable($table, 'FROM');

        if ($fields) {
            $this->addFields($tableAlias, $fields);
        }
        
        return $this;
    }

    /**
     * @param string|array $table
     * @param string $on
     * @param string|array|* $fields
     * @return $this
     */
    public function leftJoin($table, $on, $fields = null)
    {
        $tableAlias = $this->addTable($table, 'LEFT JOIN', $on);

        if ($fields) {
            $this->addFields($tableAlias, $fields);
        }
        
        return $this;
    }

    /**
     * @param string|array $table
     * @param string $on
     * @param string|array|* $fields
     * @return $this
     */
    public function rightJoin($table, $on, $fields = null)
    {
        $tableAlias = $this->addTable($table, 'RIGHT JOIN', $on);

        if ($fields) {
            $this->addFields($tableAlias, $fields);
        }

        return $this;
    }

    /**
     * @param string|array $table
     * @param string $on
     * @param string|array|* $fields
     * @return $this
     */
    public function innerJoin($table, $on, $fields = null)
    {
        $tableAlias = $this->addTable($table, 'INNER JOIN', $on);

        if ($fields) {
            $this->addFields($tableAlias, $fields);
        }

        return $this;
    }

    /**
     * @param string|array $condition
     * @param null $value
     * @return $this
     */
    public function where($condition, $value = null)
    {
        $this->getWhere()->add($condition, $value);

        return $this;
    }

    /**
     * @param string|array $condition
     * @param null|mixed $value
     * @return $this
     */
    public function orWhere($condition, $value = null)
    {
        $this->getWhere()->addOr($condition, $value);

        return $this;
    }

    /**
     * @return Where
     */
    public function getWhere()
    {
        if (null === $this->sql['where']) {
            $this->sql['where'] = new Where($this->connection);
        }
        return $this->sql['where'];
    }

    public function order($order)
    {
        $this->sql['order'][] = $order;

        return $this;
    }

    public function limit($count, $offset = 0)
    {
        if ($count) {
            $this->sql['limit'] = array($offset, $count);
        }
        
        return $this;
    }

    public function paging($page, $pagesize)
    {
        $this->limit($pagesize, ($page - 1) * $pagesize);
        
        return $this;
    }

    public function toTotalCountSQL()
    {
        $s = array();
        foreach($this->sql as $method => $meta) {
            if ($method === 'from') {
                $s[] = $this->parseFrom($meta, true);
            } else if ($method === 'limit') {
                continue;
            } else {
                $method = 'parse' . ucfirst($method);
                $r = $this->$method($meta);
                if ($r)
                    $s[] = $r;
            }
        }
        return implode(' ', $s);
    }

    public function toSQL()
    {
        $s = array();
        foreach($this->sql as $method => $meta) {
            if ($method === 'from') {
                $s[] = $this->parseFrom($meta, false);
            } else {
                $method = 'parse' . ucfirst($method);
                $r = $this->$method($meta);
                if ($r)
                    $s[] = $r;
            }
        }
        return implode(' ', $s);
    }

    protected function parseFrom($from, $isCountField = false)
    {
        $a = $f = array();
        foreach($from as $tableAlias => $meta) {
            $s = "{$meta['type']} {$meta['table']} {$tableAlias}";
            if ($meta['on'] !== null)
                $s .= " ON {$meta['on']}";
            $a[] = $s;
            
            if (! $isCountField) {
                if ($meta['fields']) {
                    foreach($meta['fields'] as $alias => $field) {
                        $f[] = "{$tableAlias}.{$field} {$alias}";
                    }
                }
            }
        }
        
        $a = implode(' ', $a);
        
        if ($isCountField) {
            $f = 'COUNT(*)';
        } else {
            $f = implode(',', $f);
        }
        
        return "SELECT {$f} {$a}";
    }

    protected function parseWhere($where)
    {
        $where = (string) $where;
        if ($where) {
            return ' WHERE ' . (string) $where;
        }
        return '';
    }

    protected function parseOrder($order)
    {
        if ($order) {
            return 'ORDER BY ' . implode(',', $order);
        }
        return '';
    }

    protected function parseLimit($limit)
    {
        if ($limit) {
            return "LIMIT {$limit[0]}, $limit[1]";
        }
        return '';
    }

    protected function addTable($table, $type, $on = null)
    {
        if (is_array($table)) {
            list($table, $alias) = $table;
        }

        if (!isset($alias)) {
            $alias = $table;
        }

        if (isset($this->sql['from'][$alias])) {
            throw new \RuntimeException(sprintf('The table alias[%s] has already exist.', $alias));
        }

        $this->sql['from'][$alias] = array(
            'fields' => array(),
            'type' => $type,
            'on' => $on,
            'table' => $table
        );

        return $alias;
    }
}
