<?php

namespace Youngx\MVC\ListView;

use Youngx\Database\Entity;
use Youngx\MVC\Context;
use Youngx\MVC\Html;
use Youngx\MVC\ListView;

class Column implements ColumnInterface
{
    private $name;
    private $label;
    protected $sortable = false;

    /**
     * @var ListView
     */
    private $listView;

    public function __construct($name, $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function format(Context $context, Entity $entity, Html $html)
    {
        if ($this->listView) {
            $this->listView->formatColumn($this, $entity, $html);
        } else {
            $html->setContent($entity->get($this->getName()));
        }
    }

    public function setListView(ListView $listView)
    {
        $this->listView = $listView;

        return $this;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function sortable($sortable = true)
    {
        $this->sortable = $sortable;

        return $this;
    }
}