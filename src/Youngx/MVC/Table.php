<?php

namespace Youngx\MVC;

use Youngx\MVC\ListView\ColumnInterface;

abstract class Table extends ListView
{
    public function type()
    {
        return 'table';
    }

    public function getBatchName()
    {
        return null;
    }

    public function formatColumnHeading(ColumnInterface $column, Html $th)
    {
        if ($column->isSortable()) {
            $th->setContent(
                $this->context->html('a', array(
                        '#content' => $th->getContent(),
                        'href' => $this->generateSortingUrl($column->getName(), $this->context)
                    ))
            );
        }
        $method = 'format' . $this->resolveNameMethod($column->getName()) . 'ColumnHeading';
        if (method_exists($this, $method)) {
            $this->$method($th, $column);
        }
    }
}