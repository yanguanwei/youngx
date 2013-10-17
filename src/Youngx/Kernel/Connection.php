<?php

namespace Youngx\Database;

class Connection
{
    protected $type;
    protected $host;
    protected $dbname;
    protected $user;
    protected $password;
    protected $charset;
    protected $tablePrefix;
    
    /**
     *
     * @var \PDO
     */
    protected $pdo;

    public function __construct($dbname, $user, $password = '', $tablePrefix = '', $host = 'localhost', $type = 'mysql', $charset = 'UTF8')
    {
        $this->type = $type;
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
        $this->charset = $charset;
        $this->tablePrefix = $tablePrefix;
    }

    public function beginTransaction()
    {
        return $this->getPDO()->beginTransaction();
    }

    public function commit()
    {
        return $this->getPDO()->commit();
    }

    /**
     *
     * @param string $table            
     * @param string|array|Where $where            
     * @return int
     */
    public function count($table, $where = null)
    {
        if (is_array($where))
            $where = $this->where($where);
        
        $where = (string) $where;
        $sql = "SELECT COUNT(*) FROM {$table}" . $where ? " WHERE {$where}" : '';
        
        return intval($this->query($sql)->fetchColumn(0));
    }

    public function rollBack()
    {
        return $this->getPDO()->rollBack();
    }

    /**
     *
     * @param string $table            
     * @param string $where            
     * @param array $params            
     * @throws Exception
     * @return boolean
     */
    public function delete($table, $where, array $params = array())
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->exec($sql, $params);
    }

    public function drop($table)
    {
        return $this->exec("DELETE FROM {$table}");
    }

    /**
     *
     * @param string $sql            
     * @throws ConnectionException
     * @return int
     */
    public function exec($sql, array $params = array())
    {
        $sql = (string) $sql;
        if ($params) {
            $result = $this->prepare($sql)->execute($this->parseParams($params));
        } else {
            $sql = $this->parseTablePrefix($sql);
            $result = $this->getPDO()->exec($sql);
        }
        if (false === $result)
            $this->throwException($sql);
        return $result;
    }

    public function fetch($table, $columns, $where = null, $order = null)
    {
        if ($columns === null) {
            $columns = '*';
        } else if (is_array($columns)) {
            $columns = implode(',', $columns);
        }
        
        $where = (string) $where;
        $sql = "SELECT {$columns} FROM {$table}" . ($where ? " WHERE {$where}" : '') . ($order ? " ORDER BY {$order}" : '');
        
        return $this->query($sql)->fetch();
    }

    /**
     *
     * @return \PDO
     */
    public function getPDO()
    {
        if (null === $this->pdo) {
            $dsn = "{$this->drive}:host={$this->host};dbname={$this->dbname}";
            $this->pdo = $pdo = new \PDO($dsn, $this->user, $this->password, array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->charset}'"
            ));
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return $this->pdo;
    }

    /**
     *
     * @param string $table            
     * @param array $data            
     * @throws ConnectionException
     * @return int
     */
    public function insert($table, array $data)
    {
        $keys = implode(',', array_keys($data));
        $values = array();
        foreach($data as $k => $v) {
            $values[] = ":{$k}";
        }
        $values = implode(',', $values);
        $sql = "INSERT INTO {$table} ({$keys}) VALUES ($values)";
        $this->exec($sql, $data);
        return $this->getPDO()->lastInsertId();
    }

    /**
     *
     * @param string $sql            
     * @throws ConnectionException
     * @return \PDOStatement
     */
    public function prepare($sql)
    {
        $sql = (string) $sql;
        $sql = $this->parseTablePrefix($sql);
        $result = $this->getPDO()->prepare($sql);
        if (false === $result)
            $this->throwException($sql);
        return $result;
    }

    /**
     *
     * @param string $sql            
     * @throws ConnectionException
     * @return \PDOStatement
     */
    public function query($sql, array $params = array())
    {
        $sql = (string) $sql;
        if ($params) {
            $stmt = $this->prepare($sql);
            $stmt->execute($this->parseParams($params));
        } else {
            $sql = $this->parseTablePrefix($sql);
            $stmt = $this->getPDO()->query($sql);
        }
        
        if (false === $stmt)
            $this->throwException($sql);
        
        return $stmt;
    }

    /**
     * array(12, 34, 45) -> '12', '34', '45'
     *
     * @param int|string|array $value            
     * @param null|int $type            
     * @return string
     */
    public function quote($value, $type = null)
    {
        if (is_array($value)) {
            foreach($value as &$val) {
                $val = $this->quote($val, $type);
            }
            return implode(', ', $value);
        }
        
        return $this->getPDO()->quote($value, $type);
    }

    /**
     *
     * @param string $text            
     * @param string|array $value            
     * @param mixed $type            
     * @param null|int $count            
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                -- $count;
            }
            return $text;
        }
    }

    public function quoteLike($value)
    {
        return strtr($value, array(
            "\\" => "\\\\",'_' => '\_','%' => '\%',"'" => "\\'"
        ));
    }

    public function quoteTableName($name)
    {
        return '`' . $name . '`';
    }

    public function quoteColumnName($name)
    {
        return '`' . $name . '`';
    }

    /**
     *
     * @param string|array($table, $alias) $table
     * @param null|string|array $fields            
     * @return young\database\SelectSQL
     */
    public function select($table, $fields = null)
    {
        $select = new SelectSQL($this);
        $select->from($table, $fields);
        return $select;
    }

    /**
     *
     * @param string $table            
     * @param array $data            
     * @param string $where            
     * @throws ConnectionException
     * @return int
     */
    public function update($table, array $data, $where = null, array $params = array())
    {
        $set = array();
        foreach($data as $k => $v)
            $set[] = "{$k}=:{$k}";
        $set = implode(',', $set);
        $sql = "UPDATE {$table} SET {$set}";
        $where = (string) $where;
        if ($where)
            $sql .= " WHERE {$where}";
        return $this->exec($sql, array_merge($params, $data));
    }

    /**
     *
     * @return \Youngx\Database\Where
     */
    public function where(array $cond = array())
    {
        $where = new Where($this);
        if ($cond)
            $where->add($cond);
        return $where;
    }

    protected function parseParams(array $params)
    {
        $a = array();
        foreach($params as $k => $v) {
            if (is_string($k))
                $a[":{$k}"] = $v;
            else
                $a[$k] = $v;
        }
        return $a;
    }

    protected function parseTablePrefix($sql)
    {
        if (preg_match_all('/\{\{(.+?)\}\}/', $sql, $matches)) {
            $replace = array();
            foreach($matches[1] as $table) {
                $replace[] = $this->tablePrefix . $table;
            }
            $sql = str_replace($matches[0], $replace, $sql);
        }
        return $sql;
    }

    protected function throwException($sql)
    {
        $e = $this->getPDO()->errorInfo();
        $message = "sql: {$sql}\n" . $e[2];
        throw new ConnectionException($sql, $message, $e[0]);
    }
}
