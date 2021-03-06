<?php

namespace Youngx\Database;

class Connection
{
    protected $temporaryNameIndex = 0;

    protected $queries = array();
    protected $type;
    protected $host;
    protected $dbname;
    protected $user;
    protected $password;
    protected $charset;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct($dbname, $user, $password = '', $host = 'localhost', $type = 'mysql', $charset = 'UTF8')
    {
        $this->type = $type;
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
        $this->charset = $charset;
    }

    public function beginTransaction()
    {
        return $this->getPDO()->beginTransaction();
    }

    public function inTransaction()
    {
        return $this->getPDO()->inTransaction();
    }

    public function commit()
    {
        return $this->getPDO()->commit();
    }

    /**
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
     * @return boolean
     */
    public function delete($table, $where = null, array $params = array())
    {
        $sql = "DELETE FROM {$table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        };

        return $this->exec($sql, $params);
    }

    public function drop($table)
    {
        return $this->exec("DELETE FROM {$table}");
    }

    /**
     *
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function exec($sql, array $params = array())
    {
        $stmt = null;

        if ($params) {
            $stmt = $this->prepare($sql);
            $result = $stmt->execute($this->parseParams($params));
        } else {
            $this->queries[] = $sql = (string) $sql;
            $result = $this->getPDO()->exec($sql);
        }

        if (false === $result) {
            $this->throwException($stmt);
        }

        return $stmt ? $stmt->rowCount() : $result;
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
            $dsn = "{$this->type}:host={$this->host};dbname={$this->dbname}";
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

    public function insertMultiple($table, array $multiRowData)
    {
        $keys = implode(',', array_keys(reset($multiRowData)));
        $values = array();
        foreach ($multiRowData as $data) {
            $row = array();
            foreach ($data as $v) {
                $row[] = $this->quote($v);
            }
            $values[] = '('. implode(',', $row) . ')';
        }
        $values = implode(',', $values);
        $sql = "INSERT INTO {$table} ({$keys}) VALUES {$values}";
        $this->exec($sql);
    }
    /**
     *
     * @param string $sql
     * @throws ConnectionException
     * @return \PDOStatement
     */
    public function prepare($sql)
    {
        $this->queries[] = $sql = (string) $sql;
        return $this->getPDO()->prepare($sql);
    }

    /**
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, array $params = array())
    {
        $stmt = $this->prepare($sql);
        $result = $stmt->execute($this->parseParams($params));
        if (false === $result) {
            $this->throwException($stmt);
        }
        return $stmt;
    }

    public function createTemporary($sql, array $params = array())
    {
        $table = $this->generateTemporaryTableName();
        if ($this->exec('CREATE TEMPORARY TABLE ' . $table . ' Engine=MEMORY ' . "({$sql})", $params)) {
            return $table;
        }
    }

    protected function generateTemporaryTableName()
    {
        return "db_temporary_" . $this->temporaryNameIndex++;
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
     * @return mixed|string
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

    /**
     *
     * @param string|array($table, $alias) $table
     * @param null|string|array $fields
     * @return Select
     */
    public function select($table, $fields = null)
    {
        $select = new Select($this);
        $select->from($table, $fields);
        return $select;
    }

    /**
     *
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $params
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
     * @param array $condition
     * @return Where
     */
    public function where(array $condition = array())
    {
        $where = new Where($this);
        if ($condition)
            $where->add($condition);
        return $where;
    }

    public function getQueries()
    {
        return $this->queries;
    }

    protected function parseParams(array $params)
    {
        $a = array();
        foreach($params as $k => $v) {
            if (is_string($k) && $k[0] !== ':')
                $a[":{$k}"] = $v;
            else
                $a[$k] = $v;
        }
        return $a;
    }

    protected function throwException(\PDOStatement $stmt = null)
    {
        $sql = end($this->queries);
        $e = $stmt ? $stmt->errorInfo() : $this->getPDO()->errorInfo();
        $message = "{$e[2]}\n" . "sql: {$sql}";
        throw new ConnectionException($sql, $message, $e[1], $e[0]);
    }
}