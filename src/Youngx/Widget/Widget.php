<?php

namespace Youngx\Kernel;

use Youngx\EventHandler\Handler;
use Youngx\EventHandler\HandlerAware;

class Widget implements HandlerAware
{
    private static $_id = 0;

    private $config = array();

    /**
     * @var Handler
     */
    protected $handler;

    public function __construct(array $config = array())
    {
        foreach ($config as $key => $value) {
            if ($key[0] === '#') {
                $this->config[substr($key, 1)] = $value;
                unset($config[$key]);
            }
        }

        foreach ($config as $key => $value) {
            if (method_exists($this, $method = 'set' .  (strpos($key, '_') === false ? ucfirst($key) : implode(array_map('ucfirst', explode('_', $key)))))) {
                $this->$method($value);
            } else {
                $this->$key = $value;
            }
        }

        $this->init();
    }

    protected function init()
    {
    }

    public function __toString()
    {
        try {
            $this->start();
            return (string) $this->end();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function start()
    {
        $class = get_class($this);
        if (strncmp(__CLASS__, $class, strlen(__CLASS__)) === 0) {
            $name = substr($class, strrpos($class, '\\') + 1);
        } else {
            $parts = explode('\\', $class);
            $name = array_pop($parts);
            array_pop($parts);
            $name = substr(array_pop($name), 0, -6) . '.' . $name;
        }

        $name = substr($name, 0, -6);
        $this->handler->trigger("kernel.widget.{$name}", $this);

        ob_start();

        $this->run();

        return $this;
    }

    protected function run()
    {
    }

    protected function format($content)
    {
        return $content;
    }

    public function end()
    {
        return $this->format(ob_get_clean());
    }

    protected function generateId()
    {
        return 'yw' . self::$_id++;
    }

    public function hasConfig($key)
    {
        return isset($this->config[$key]);
    }

    public function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function setHandler(Handler $handler)
    {
        $this->handler = $handler;
    }
}