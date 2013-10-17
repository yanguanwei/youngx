<?php

namespace Youngx\Kernel\ListView;

use Youngx\Kernel\Database\Entity;
use Youngx\Kernel\ListView;
use Youngx\Kernel\ListView\TableListView\Column;

class TableListView extends ListView
{
    private $unSortedColumns = array();
    private $columns;

    /**
     * @param $name
     * @param $label
     * @param int $sort
     * @return Column
     */
    protected function addColumn($name, $label, $sort = 0)
    {
        return $this->unSortedColumns[$sort][$name] = new Column($this, $name, $label);
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        if (null === $this->columns) {
            if ($this->unSortedColumns) {
                $sortedColumns = $this->unSortedColumns;
                ksort($sortedColumns);
                $this->columns = call_user_func_array('array_merge', $sortedColumns);
            } else {
                $this->columns = array();
            }
        }
        return $this->columns;
    }

    public function renderColumn(Entity $entity, Column $column)
    {
        $method = 'render' . ucfirst($column->getName()) . 'Column';
        if (method_exists($this, $method)) {
            return $this->$method($entity);
        }
    }

    /**
     * @param Entity $entity
     * @param $name
     * @return string
     */
    public function renderCell(Entity $entity, $name)
    {
        $method = 'render' . ucfirst($name) . 'Cell';
        if (method_exists($this, $method)) {
            return $this->$method($entity);
        } else {
            return $entity->get($name);
        }
    }
}