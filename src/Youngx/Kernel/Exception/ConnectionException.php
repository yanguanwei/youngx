<?php

namespace Youngx\Database;

class ConnectionException extends \Exception
{
    protected $sql;

    public function __construct($sql, $message, $code)
    {
        $this->sql = $sql;
        parent::__construct($message, $code);
    }

    public function getSql()
    {
        return $this->sql;
    }
}
