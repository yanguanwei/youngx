<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Context;
use Youngx\MVC\Html;
use Youngx\MVC\Widget;

class BreadcrumbsWidget extends Widget
{
    protected $breadcrumbs = array();
    /**
     * @var Html
     */
    protected $wrapHtml;
    /**
     * @var Html[]
     */
    protected $itemHtmls;

    public function name()
    {
        return 'breadcrumbs';
    }

    protected function format($content)
    {
        return $this->getWrapHtml()->setContent($content . implode("\n", $this->getItemHtmls()));
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        if (null === $this->wrapHtml) {
            $this->wrapHtml = $this->context->html('ul');
        }
        return $this->wrapHtml;
    }

    /**
     * @return Html[]
     */
    public function getItemHtmls()
    {
        if (null === $this->itemHtmls) {
            $itemHtmls = array();
            foreach ($this->breadcrumbs as $item) {
                $itemHtmls[] = $this->context->html('li')->append($this->context->html('a', array(
                                'href' => $item['url'],
                                '#content' => $item['label']
                            )), 'link');
            }
            $this->itemHtmls = $itemHtmls;
        }
        return $this->itemHtmls;
    }

    /**
     * @param array $breadcrumbs
     */
    public function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }
}