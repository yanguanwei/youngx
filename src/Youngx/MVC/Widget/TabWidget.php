<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Html;
use Youngx\MVC\Widget;

class TabWidget extends Widget
{
    protected $tabs = array();
    protected $active;
    protected $activeClass = 'active';

    private $position;

    /**
     * @var Html
     */
    private $wrapHtml;
    /**
     * @var Html
     */
    private $tabHtml;
    /**
     * @var Html
     */
    private $contentWrapHtml;
    /**
     * @var Html[]
     */
    private $tabListHtmls;
    /**
     * @var Html[]
     */
    private $contentHtmls;
    /**
     * @var Html
     */
    private $currentContentHtml;

    public function name()
    {
        return 'tab';
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        if (null === $this->wrapHtml) {
            $this->wrapHtml = $this->context->html('div');
        }
        return $this->wrapHtml;
    }

    /**
     * @return Html[]
     */
    public function getContentHtmls()
    {
        return $this->contentHtmls;
    }

    /**
     * @return Html
     */
    publIc function getContentWrapHtml()
    {
        if (null === $this->contentWrapHtml) {
            $this->contentWrapHtml = $this->context->html('div');
        }
        return $this->contentWrapHtml;
    }

    /**
     * @return Html
     */
    public function getTabHtml()
    {
        if (null === $this->tabHtml) {
            $this->tabHtml = $this->context->html('ul');
        }
        return $this->tabHtml;
    }

    /**
     * @return Html[]
     */
    public function getTabListHtmls()
    {
        if (null === $this->tabListHtmls) {
            $htmls = array();
            foreach ($this->tabs as $id => $config) {
                $htmls[$id] = $this->context->html('li',array('class' => $id == $this->active ? $this->activeClass : ''))
                    ->append($a = $this->context->html('a', $config), 'link');
                if (is_string($config)) {
                    $a->set('href', "#{$id}");
                }
            }
            $this->tabListHtmls = $htmls;
        }
        return $this->tabListHtmls;
    }

    /**
     * @param $id
     * @return Html|null
     */
    public function getContentHtml($id)
    {
        return isset($this->contentHtmls[$id]) ? $this->contentHtmls[$id] : null;
    }

    public function content($id, $content, array $attributes = array())
    {
        $this->startContent($id, $attributes);
        echo $content;
        $this->endContent();

        return $this;
    }

    public function startContent($id, array $attributes = array())
    {
        $this->contentHtmls[$id] = $this->currentContentHtml = $this->context->html('div');

        $attributes['id'] = $id;
        if ($attributes) {
            $this->currentContentHtml->set($attributes);
        }

        if ($id == $this->active) {
            $this->currentContentHtml->addClass($this->activeClass);
        }

        ob_start();

        return $this;
    }

    public function endContent()
    {
        $this->currentContentHtml->setContent(ob_get_clean());
        $this->getContentWrapHtml()->append($this->currentContentHtml);
        $this->currentContentHtml = null;
    }

    protected function format($content)
    {
        $list =  $this->getTabHtml()
            ->setContent(implode("\n", $this->getTabListHtmls()));

        $content = $this->getContentWrapHtml()->setContent($content);

        return $this->getWrapHtml()->setContent($list . $content);
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $activeClass
     */
    public function setActiveClass($activeClass)
    {
        $this->activeClass = $activeClass;
    }

    /**
     * @return string
     */
    public function getActiveClass()
    {
        return $this->activeClass;
    }

    /**
     * @param array $tabs
     */
    public function setTabs($tabs)
    {
        $this->tabs = $tabs;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }
}