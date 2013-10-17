<?php

namespace Youngx\Database;

class SelectSQL
{
    private $_sql = array(
        'from' => array(),
        'where' => null,
        'order' => array(),
        'limit' => array()
    );
    protected $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function __toString()
    {
        return (string) $this->toSQL();
    }

    public function from($table, $fields = null)
    {
        $this->addColumns($table, $fields, 'FROM');
        
        return $this;
    }

    public function leftJoin($table, $fields, $on)
    {
        $this->addColumns($table, $fields, 'LEFTJOIN', $on);
        
        return $this;
    }

    /**
     *
     * @param Youngx\Database\Where|string $where            
     */
    public function where($where)
    {
        $this->_sql['where'] = $where;
        
        return $this;
    }

    public function order($column, $order = null)
    {
        if ($order === null) {
            $this->_sql['order'][] = $column;
        } else {
            $this->order("{$this->conn->quoteColumnName($column)} {$order}");
        }
        
        return $this;
    }

    public function limit($count, $offset = 0)
    {
        $this->_sql['limit'] = array(
            $offset,$count
        );
        
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
        foreach($this->_sql as $method => $meta) {
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
        foreach($this->_sql as $method => $meta) {
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
        $a = array();
        $f = array();
        foreach($from as $table => $meta) {
            $s = "{$meta['type']} {$this->conn->quoteTableName($table)} {$meta['alias']}";
            if ($meta['on'] !== null)
                $s .= " ON {$meta['on']}";
            $a[] = $s;
            
            if (! $isCountField) {
                if ($meta['columns']) {
                    foreach($meta['columns'] as $column) {
                        if ($column !== '*')
                            $column = $this->conn->quoteColumnName($column);
                        $f[] = "{$meta['alias']}.{$column}";
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
        if ($where)
            return ' WHERE ' . (string) $where;
    }

    protected function parseOrder($order)
    {
        if ($order)
            return 'ORDER BY ' . implode(',', $order);
    }

    protected function parseLimit($limit)
    {
        if ($limit)
            return "LIMIT {$limit[0]}, $limit[1]";
    }

    protected function addColumns($table, $fields, $type, $on = null)
    {
        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }
        
        if (is_array($table)) {
            list($table, $alias) = $table;
        }
        
        if (! isset($alias))
            $alias = $table;
        
        if (! isset($this->_sql['from'][$table]))
            $this->_sql['from'][$table] = array(
                'columns' => array(),'type' => $type,'on' => $on,'alias' => $alias
            );
        
        if (is_array($fields)) {
            foreach($fields as $field)
                if (! in_array($field, $this->_sql['from'][$table]['columns']))
                    $this->_sql['from'][$table]['columns'][] = trim($field);
        }
        
        return $this;
    }
}
?>