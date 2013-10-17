<?php

namespace Youngx\Bundle\CharismaBundle\Yui;

use Youngx\UI\Html;
use Youngx\Yui\Extension;

class CharismaExtension implements Extension
{
    /**
     * @var \Youngx\UI\HtmlTag[]
     */
    protected $stack = array();

    public function start()
    {
        ob_start();
        return $this;
    }

    public function end()
    {
        $html = array_pop($this->stack);
        $content = ob_get_clean();

        if (!$html) {
            throw new \RuntimeException('There no more to be end.');
        }

        $html->value($content);

        echo $html;
    }

    public function rowFluid(array $attributes = array())
    {
        $this->stack[] = Html::tag('div', array('class' => 'row-fluid'), '')->attr($attributes);

        return $this;
    }

    public function box($span, array $attributes = array())
    {
        $this->span($span, array('class' => 'box'));
    }

    public function span($span, array $attributes = array())
    {
        $this->stack[] = Html::tag('div', array('class' => "span($span}"), '')->attr($attributes);

        return $this;
    }

    /**
     * @return \Youngx\UI\HtmlTag
     */
    public function getHtml()
    {
        return end($this->stack);
    }

    public static function registerMethods()
    {
        return array(

        );
    }
}