<?php

namespace Youngx\MVC\ListView;

use Youngx\MVC\ListView;
use Youngx\Util\SortableArray;

class ColumnCollection
{
    protected $listView;
    /**
     * @var SortableArray
     */
    protected $columns;

    public function __construct(ListView $listView)
    {
        $this->listView = $listView;
        $this->columns = new SortableArray();
    }

    /**
     * @param $name
     * @param $label
     * @param int $sort
     * @return ColumnInterface
     */
    public function add($name, $label = null, $sort = 0)
    {
        $this->addColumn($column = new Column($name, $label), $sort);

        return $column;
    }

    /**
     * @param ColumnInterface $column
     * @param int $sort
     * @return ColumnInterface
     */
    public function addColumn(ColumnInterface $column, $sort = 0)
    {
        $this->columns->set($column->getName(), $column, $sort);
        if (method_exists($column, 'setListView')) {
            $column->setListView($this->listView);
        }

        return $column;
    }

    public function sort($name, $sort)
    {
        $this->columns->sort($name, $sort);

        return $this;
    }

    /**
     * @param string $name
     * @return ColumnInterface | null
     */
    public function get($name)
    {
        return $this->columns->get($name);
    }

    public function remove($name)
    {
        $this->columns->remove($name);

        return $this;
    }

    /**
     * @return ColumnInterface[]
     */
    public function all()
    {
         return $this->columns->all();
    }
}