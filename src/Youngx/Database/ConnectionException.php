<?php

namespace Youngx\Database;

class ConnectionException extends \Exception
{
    protected $sql;
    protected $SQLState;

    public function __construct($sql, $message, $code, $SQLState)
    {
        $this->sql = $sql;
        $this->SQLState = $SQLState;
        parent::__construct($message, $code);
    }

    public function getSQLState()
    {
        return $this->SQLState;
    }

    public function getSQL()
    {
        return $this->sql;
    }
}
