<?php

namespace Youngx\MVC;

use Youngx\Util\SortableArray;

abstract class Widget
{
    private static $_id = 0;
    private $formatted = false;
    private $file;
    private $variables;

    private $skin = 'default';
    private $attributes = array();
    private $contents;
    protected $wrappers;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context, array $config)
    {
        $this->context = $context;
        $this->set($config);
        $this->init();
    }

    public function __toString()
    {
        try {
            $this->start();
            $s = (string) $this->end();
            if ($this->wrappers) {
                foreach ($this->getWrappers()->all() as $widget) {
                    if ($widget instanceof Widget) {
                        $widget->getContents()->set($this->name(), $s);
                        $s = (string) $widget;
                    }
                }
            }
            return $s;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    abstract public function name();

    public function wrap($name, Widget $widget, $sort = 0)
    {
        $this->getWrappers()->set($name, $widget, $sort);

        return $this;
    }

    public function start()
    {
        $this->setup();
        ob_start();
        $this->run();
        return $this;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            if ($key[0] === '#') {
                $config = substr($key, 1);
                if (method_exists($this, $method = 'set'.ucfirst($config))) {
                    $this->$method($value);
                } else {
                    throw new \Exception('Unknown config key: ' .$key);
                }
            } else {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    protected function init()
    {
    }

    protected function setup()
    {
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
        $content = ob_get_clean();
        if ($content) {
            $this->getContents()->set('_content_', $content);
        }

        if ($this->file) {
            $this->getContents()->set('_file_', $this->context->render($this->file, array_merge($this->variables, array(
                            'widget' => $this
                        ))));
        }

        if (!$this->formatted) {
            $name = $this->name();
            $this->context->handler()->triggerWithMenu(
                array("kernel.widget.format#{$name}", "kernel.widget.format#{$name}@skin:{$this->skin}"),
                $this,
                $name
            );
            $this->formatted = true;
        }

        if ($this->contents) {
            $content = implode("\n", $this->getContents()->all());
        } else {
            $content = '';
        }

        return $this->format($content);
    }

    protected function generateId()
    {
        return 'yw' . self::$_id++;
    }

    public function get($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    public function formatted($formatted)
    {
        $this->formatted = $formatted;

        return $this;
    }

    public function render($file, array $variables = array())
    {
        $this->file = $file;
        $this->variables = $variables;

        return $this;
    }

    public function setSkin($skin)
    {
        $this->skin = $skin;

        return $this;
    }

    public function getSkin()
    {
        return $this->skin;
    }

    /**
     * @return SortableArray
     */
    public function getContents()
    {
        if (null === $this->contents) {
            $this->contents = new SortableArray();
        }
        return $this->contents;
    }

    /**
     * @return SortableArray
     */
    protected function getWrappers()
    {
        if (null == $this->wrappers) {
            $this->wrappers = new SortableArray();
        }
        return $this->wrappers;
    }
}