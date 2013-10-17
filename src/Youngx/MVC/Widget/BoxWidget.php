<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Widget;
use Youngx\MVC\Html;
use Youngx\Util\SortableArray;

class BoxWidget extends Widget
{
    protected $header = array();
    protected $body = array();
    protected $title = array();

    /**
     * @var Html
     */
    private $wrapHtml;
    /**
     * @var Html
     */
    private $headerHtml;
    /**
     * @var Html
     */
    private $bodyHtml;
    /**
     * @var Html
     */
    private $titleHtml;

    /**
     * @var SortableArray
     */
    private $toolbars;

    protected function format($content)
    {
        if ($content) {
            $this->getBodyHtml()->append($content, 'content');
        }

        return $this->getWrapHtml()->setContent(
            $this->getHeaderHtml()->setContent($this->getTitleHtml() . implode("\n", $this->getToolbarHtmls()))
            . $this->getBodyHtml()
        );
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        if (null  === $this->wrapHtml) {
            $this->wrapHtml = $this->context->html('div');
        }

        return $this->wrapHtml;
    }

    /**
     * @return Html
     */
    public function getHeaderHtml()
    {
        if (null === $this->headerHtml) {
            $this->headerHtml = $this->context->html('div', $this->header);
        }
        return $this->headerHtml;
    }

    /**
     * @return Html
     */
    public function getTitleHtml()
    {
        if (null === $this->titleHtml) {
            $this->titleHtml = $this->context->html('h5', is_array($this->title) ? $this->title : array(
                    '#content' => $this->title
                ));
        }
        return $this->titleHtml;
    }

    /**
     * @param $key
     * @param $content
     * @param int $sort
     * @return Html
     */
    public function addToolbar($key, $content, $sort = 0)
    {
        $this->getToolbarSortableArray()->set($key, $toolbarHtml = $this->context->html('div', array(
                    '#content' => $content
                )), $sort);

        return $toolbarHtml;
    }

    /**
     * @return Html[]
     */
    public function getToolbarHtmls()
    {
        return $this->toolbars ? $this->toolbars->all() : array();
    }

    public function sortToolbar($key, $sort)
    {
        $this->getToolbarSortableArray()->sort($key, $sort);

        return $this;
    }

    /**
     * @return Html
     */
    public function getBodyHtml()
    {
        if (null === $this->bodyHtml) {
            $this->bodyHtml = $this->context->html('div', is_array($this->body) ? $this->body : array(
                    '#content' => $this->body
                ));
        }
        return $this->bodyHtml;
    }

    public function name()
    {
        return 'box';
    }

    /**
     * @return SortableArray
     */
    protected function getToolbarSortableArray()
    {
        if (null === $this->toolbars) {
            $this->toolbars = new SortableArray();
        }

        return $this->toolbars;
    }

    /**
     * @param array $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * @param array $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
