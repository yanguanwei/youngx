<?php

namespace Youngx\MVC\Table;

use Youngx\Database\Query;
use Youngx\EventHandler\Event\GetArrayEvent;
use Youngx\MVC\RenderableResponse;
use Youngx\MVC\Table;
use Youngx\Util\PropertyAccess;

abstract class TreeTable extends Table
{
    public function getBatchName()
    {
        return null;
    }

    protected function doPostRequest()
    {
        $propertyAccess = new PropertyAccess($this);
        $propertyAccess->set($this->context->request()->request->all());
        $this->initColumns();
        $this->initQuery();

        $widget = $this->context->widget('TreeTable', array(
                '#table' => $this
            ));
        $json = array();
        $entities = $this->getEntities();
        foreach ($widget->getTdHtmlRows() as $i => $row) {
            $json[] = array(
                'data' => array(
                    'rows' => $row,
                    'entity' => $entities[$i]->toArray(),
                ),
                'child' => $this->hasChildForEntity($entities[$i])
            );
        }
        return $this->context->response(json_encode(array(
                    'items' => $json
                )));
    }

    protected function render(RenderableResponse $response)
    {
        $response->setContent($this->context->widget('TreeTable', array(
                    '#table' => $this
                )));
    }

    protected function paging(Query $query, GetArrayEvent $event)
    {
        if ($this->context->request()->isMethod('GET')) {
            parent::paging($query, $event);
        }
    }

    /**
     * @return string
     */
    abstract public function getPostDataForScriptCodes();

    /**
     * @param $entity
     * @return Boolean
     */
    abstract public function hasChildForEntity($entity);

    public function getPageSize()
    {
        return 50;
    }
}