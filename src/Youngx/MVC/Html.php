<?php

namespace Youngx\MVC;

use Youngx\MVC\Input\InputInterface;
use Youngx\Util\SortableArray;

class Html implements InputInterface
{
    /**
     * @var Context
     */
    protected $context;

    private static $_count = 0;

    private $tag;
    private $empty;
    private $attributes = array();
    private $config = array();
    private $htmls = array();
    private $formatted = false;
    private $formatter;
    private $value;
    private $hidden = false;
    /**
     * @var SortableArray
     */
    private $before;

    /**
     * @var SortableArray
     */
    private $after;

    /**
     * @var SortableArray
     */
    private $beforeWrapper;

    /**
     * @var SortableArray
     */
    private $afterWrapper;

    /**
     * @var SortableArray
     */
    private $wrapper;

    /**
     * @var SortableArray
     */
    private $contents;

    public function __construct(Context $context, $tag, array $attributes = array(), $formatter = null, $empty = false)
    {
        $this->context = $context;
        $this->tag = $tag;
        $this->empty = $empty;
        $this->formatter = $formatter ?: $tag;

        $this->contents = new SortableArray();
        $this->contents->set(0, null);

        if ($attributes) {
            $this->set($attributes);
        }

        $this->init();
    }

    protected function init()
    {
    }

    public function __toString()
    {
        if ($this->hidden) {
            return '';
        }

        try {
            if (!$this->formatted) {
                $formatter = $this->getFormatter();

                foreach ($this->config as $key => $value) {
                    $this->context->handler()->triggerOne(array(
                            "kernel.html#{$this->getFormatter()}@config:{$key}",
                            "kernel.html@config:{$key}"
                        ), $this, $value);
                }

                $this->format();
                $this->context->handler()->trigger("kernel.html.format#{$formatter}", $this, $formatter);
                $this->formatted = true;
            }
            return (string) $this->toString();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function addClass($class)
    {
        $this->set('class', $class);

        return $this;
    }

    public function after($content, $key = null, $sort = 0)
    {
        $this->getAfter()->set($key, $content, $sort);

        if ($key && is_object($content) && $content instanceof self) {
            $this->htmls[$key] = $content;
        }

        return $this;
    }

    public function afterWrap($content, $key = null, $sort = 0)
    {
        $this->getAfterWrapper()->set($key, $content, $sort);

        if ($key && is_object($content) && $content instanceof self) {
            $this->htmls[$key] = $content;
        }

        return $this;
    }

    public function append($content, $key = null)
    {
        $this->contain($content, $key);

        return $this;
    }

    public function sortContent($key, $sort)
    {
        $this->contents->sort($key, $sort);

        return $this;
    }

    public function before($content, $key = null, $sort = 0)
    {
        $this->getBefore()->set($key, $content, $sort);

        if ($key && is_object($content) && $content instanceof self) {
            $this->htmls[$key] = $content;
        }

        return $this;
    }

    public function beforeWrap($content, $key = null, $sort = 0)
    {
        $this->getBeforeWrapper()->set($key, $content, $sort);

        return $this;
    }

    public function contain($content, $key = null, $sort = 0)
    {
        $this->contents->set($key, $content, $sort);

        if ($key && is_object($content) && $content instanceof self) {
            $this->htmls[$key] = $content;
        }

        return $this;
    }

    /**
     * @param $key
     * @return Html | null
     */
    public function find($key)
    {
        return isset($this->htmls[$key]) ? $this->htmls[$key] : null;
    }

    public function formatted($formatted)
    {
        $this->formatted =  (Boolean) $formatted;

        return $this;
    }

    public function getContent()
    {
        return $this->contents->get(0);
    }

    public function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function get($key, $default = null)
    {
        return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
    }

    public function getFormatter()
    {
        return $this->formatter;
    }

    public function getId()
    {
        if (!$this->has('id')) {
            $this->set('id', 'elem_' . self::$_count++);
        }
        return $this->get('id');
    }

    public function getName()
    {
        return $this->get('name');
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function has($key)
    {
        return isset($this->attributes[$key]);
    }

    public function hasConfig($key)
    {
        return isset($this->config[$key]);
    }

    public function hasClass($class)
    {
        return isset($this->attributes['class']) && in_array($class, $this->attributes['class']);
    }

    public function isFormatted()
    {
        return $this->formatted;
    }

    public function visible()
    {
        return !$this->hidden;
    }

    public function prepend($content, $key = null)
    {
        $this->contain($content, $key, -1);

        return $this;
    }

    public function remove($key)
    {
        unset($this->attributes[$key]);

        return $this;
    }

    public function removeClass($class)
    {
        if (isset($this->attributes['class'])) {
            if (false !== $k = array_search($class, $this->attributes['class']))
                unset($this->attributes['class'][$k]);
        }
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
                $option = substr($key, 1);
                if (method_exists($this, $method = 'set'.ucfirst($option))) {
                    $this->$method($value);
                } else {
                    $this->config[$option] = $value;
                }
            } else {
                self::mergeAttributes($this->attributes, $key, $value);
            }
        }

        return $this;
    }

    public function setContent($content)
    {
        $this->contents->set(0, $content);

        return $this;
    }

    public function setName($name)
    {
        $this->set('name', $name);

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function style($key, $value = null)
    {
        if (null !== $value) {
            $key = array($key => $value);
        }

        $this->set('style', $key);

        return $this;
    }

    public function wrap(Html $html, $key = null, $sort = 0)
    {
        $this->getWrapper()->set($key, $html, $sort);

        if ($key) {
            $this->htmls[$key] = $html;
        }

        return $this;
    }

    protected static function mergeAttributes(array &$attributes, $key, $value)
    {
        if ($key === 'class') {
            $attributes[$key] = array_merge(isset($attributes[$key]) ? $attributes[$key]: array(), is_array($value) ? $value : explode(' ', $value));
        } else if ($key === 'id') {
            $attributes[$key] = strtr($value, array(
                    '][' => '-',
                    '[]' => '',
                    '[' => '-',
                    ']' => ''
                ));
        } else if ($key === 'style') {
            if (!is_array($value)) {
                $t = array();
                foreach (explode(';', $value) as $v) {
                    list($k, $v) = explode(':', $v);
                    $t[trim($k)] = trim($v);
                }
                $value = $t;
            }
            foreach ($value as $k => $v) {
                $attributes[$key][$k] = $v;
            }
        } else if ($value === true) {
            $attributes[$key] = $key;
        } else if ($value === false) {
            unset($attributes[$key]);
        } else {
            $attributes[$key] = $value;
        }
    }

    /**
     * @param array $attributes
     * @return string
     */
    protected static function normalizeAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'class' && is_array($value)) {
                $value = implode(' ', $value);
            } elseif ($key === 'style' && is_array($value)) {
                foreach ($value as $k => $v)
                    $value[$k] = "{$k}:{$v}";
                $value = implode(';', $value);
            }
            $attributes[$key] = $key . '="' . $value . '"';;
        }
        return implode(' ', $attributes);
    }

    protected function format()
    {
    }

    /**
     * @return SortableArray
     */
    protected function getAfter()
    {
        if (null === $this->after) {
            $this->after = new SortableArray();
        }
        return $this->after;
    }

    /**
     * @return SortableArray
     */
    protected function getBefore()
    {
        if (null === $this->before) {
            $this->before = new SortableArray();
        }
        return $this->before;
    }

    /**
     * @return SortableArray
     */
    protected function getBeforeWrapper()
    {
        if (null === $this->beforeWrapper) {
            $this->beforeWrapper = new SortableArray();
        }
        return $this->beforeWrapper;
    }

    /**
     * @return SortableArray
     */
    protected function getAfterWrapper()
    {
        if (null === $this->afterWrapper) {
            $this->afterWrapper = new SortableArray();
        }
        return $this->afterWrapper;
    }

    protected function getContents()
    {
        return implode("\n", $this->contents->all());
    }

    /**
     * @return SortableArray
     */
    protected function getWrapper()
    {
        if (null === $this->wrapper) {
            $this->wrapper = new SortableArray();
        }
        return $this->wrapper;
    }

    protected function toHtml()
    {
        $string = array();

        if ($this->before) {
            $string[] = implode("\n", $this->before->all());
        }

        $attributes = $this->attributes;
        $string[] = sprintf(
            '<%s%s%s',
            $this->getTag(),
            $attributes ? ' ' . self::normalizeAttributes($attributes) : '',
            $this->empty ? ' />' : ('>' . $this->getContents() . "</{$this->getTag()}>")
        );

        if ($this->after) {
            $string[] = implode("\n", $this->after->all());
        }

        return implode("\n", $string);
    }

    protected function onBeforeOutput()
    {
    }

    protected function onAfterOutput($string)
    {
        return $string;
    }

    protected function toString()
    {
        $this->onBeforeOutput();

        $string = array();

        if ($this->beforeWrapper) {
            $string[] = implode("\n", $this->beforeWrapper->all());
        }

        $html = $this->toHtml();
        if ($this->wrapper) {
            foreach ($this->wrapper->all() as $wrapper) {
                $html = $wrapper->setContent($html);
            }
        }
        $string[] = $html;

        if ($this->afterWrapper) {
            $string[] = implode("\n", $this->afterWrapper->all());
        }

        return $this->onAfterOutput(implode("\n", $string));
    }

    public function disable()
    {
        $this->set('disabled', true);

        return $this;
    }

    public function enable()
    {
        $this->set('disabled', false);

        return $this;
    }

    public function hide()
    {
        $this->hidden = true;

        return $this;
    }

    public function show()
    {
        $this->hidden = false;

        return $this;
    }
}