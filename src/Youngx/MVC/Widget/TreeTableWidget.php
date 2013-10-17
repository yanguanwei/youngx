<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Table\TreeTable;

class TreeTableWidget extends TableWidget
{
    public function name()
    {
        return 'tree-table';
    }

    protected function renderTBodyHtml()
    {
    }

    /**
     * @return TreeTable
     */
    public function getTable()
    {
        return parent::getTable();
    }
}