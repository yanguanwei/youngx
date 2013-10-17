<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Context;
use Youngx\MVC\Html;
use Youngx\MVC\Widget;

class NestedMenuWidget extends Widget
{
    protected $items = array();

    protected $wrapHtml;
    protected $subWrapHtmls = array();
    protected $itemHtmls = array();
    protected $itemLinkHtmls = array();
    protected $itemParentNames = array();
    protected $parentItemHtmls = array();
    protected $layers = array();

    public function name()
    {
        return 'nested-menu';
    }

    protected function init()
    {
        $context = $this->context;
        $this->wrapHtml = $context->html('ul');
        $this->nest($context, null, $this->items, $this->wrapHtml, 0);
    }

    protected function run()
    {
        echo $this->wrapHtml;
    }

    protected function nest(Context $context, $parent, array $items, Html $wrap, $layer)
    {
        foreach ($items as $name => $item) {
            $this->itemParentNames[$name] = $parent;
            $this->itemHtmls[$name] = $li = $context->html('li');
            $this->layers[$name] = $layer;

            $li->setContent($this->itemLinkHtmls[$name] = $a = $context->html('a', array(
                        'href' => $item['url'],
                        '#content' => $item['label']
                    )));

            if (isset($item['attributes']) && $item['attributes']) {
                $a->set($item['attributes']);
            }

            $wrap->append($li);

            if (isset($item['subMenus']) && $item['subMenus']) {
                $this->parentItemHtmls[$name] = $li;
                $li->append($this->subWrapHtmls[$name] = $subWrap = $context->html('ul'));
                $this->nest($context, $name, $item['subMenus'], $subWrap, $layer + 1);
            }
        }
    }

    /**
     * @param $name
     * @return string | null
     */
    public function getItemParentName($name)
    {
        return isset($this->itemParentNames[$name]) ? $this->itemParentNames[$name] : null;
    }

    public function getItemParentNames()
    {
        return $this->itemParentNames;
    }

    public function getLayer($name)
    {
        return $this->layers[$name];
    }

    /**
     * @param $name
     * @return Html | null
     */
    public function getItemHtml($name)
    {
        return isset($this->itemHtmls[$name]) ? $this->itemHtmls[$name] : null;
    }

    /**
     * @return array
     */
    public function getSubItemNames()
    {
        $items = array();
        foreach ($this->itemParentNames as $name => $parent) {
            if ($parent) {
                $items[] = $name;
            }
        }
        return $items;
    }

    /**
     * @param $name
     * @return Html | null
     */
    public function getItemLinkHtml($name)
    {
        return isset($this->itemLinkHtmls[$name]) ? $this->itemLinkHtmls[$name] : null;
    }

    /**
     * @param $name
     * @return Html | null
     */
    public function getSubWrapHtml($name)
    {
        return isset($this->subWrapHtmls[$name]) ? $this->subWrapHtmls[$name] : null;
    }

    /**
     * @return Html[]
     */
    public function getSubWrapHtmls()
    {
        return $this->subWrapHtmls;
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        return $this->wrapHtml;
    }

    /**
     * @return Html[]
     */
    public function getParentItemHtmls()
    {
        return $this->parentItemHtmls;
    }

    /**
     * @param array $items
     */
    protected function setItems($items)
    {
        $this->items = $items;
    }
}