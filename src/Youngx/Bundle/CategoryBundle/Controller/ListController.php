<?php

namespace Youngx\Bundle\CategoryBundle\Controller;

use Youngx\Kernel\Container as Y;
use Youngx\Bundle\CategoryBundle\Entity\Category;
use Youngx\Kernel\ListView\TableListView;

class ListViewController extends TableListView
{
    public function indexAction()
    {
        $this->addColumn('id', 'ID')->sortable();
        $this->addColumn('label', '名称', 1)->sortable();

        $operations = $this->addColumn('operations', '操作', 2);
        $operations->addCell('edit');
        $operations->addCell('delete');

        $this->execute(Category::build());

        return Y::renderResponse('admin/table.html.yui', array(
                'listView' => $this
            ));
    }

    protected function renderEditCell(Category $channel)
    {
        return Y::element('a', '编辑', array(
                'href' => Y::generateUrl('category.edit', array(
                        'category' => $channel->id
                    ))
            ));
    }

    protected function renderDeleteCell(Category $channel)
    {
        return Y::element('a', '删除', array(
                'href' => Y::generateUrl('category.delete', array(
                        'category' => $channel->id
                    ))
            ));
    }
}