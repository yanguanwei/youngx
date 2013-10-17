<?php

namespace Youngx\DI\Argument;

class ParameterReference
{
    private $key;
    private $default;
    
    public function __construct($key, $default = null)
    {
        $this->key = $key;
        $this->default = $default;
    }

    protected function resolveValue($value)
    {
        if (is_string($value)) {
            return "'" . strtr($value, "'", "\\'") . "'";
        } else if (is_array($value)) {
            return var_export($value, true);
        } else if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } else if (null === $value) {
            return 'null';
        } else {
            return (string) $value;
        }
    }

    public function __toString()
    {
        $default = $this->resolveValue($this->default);
        return "\$this->getParameter('{$this->key}', {$default})";
    }
}
