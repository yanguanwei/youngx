<?php

namespace Youngx\MVC;

use Youngx\Database\Query;
use Youngx\Database\Entity;
use Youngx\EventHandler\Event\GetArrayEvent;
use Youngx\MVC\Html;
use Youngx\MVC\ListView\ColumnCollection;
use Youngx\MVC\ListView\ColumnInterface;

abstract class ListView extends Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ColumnInterface[]
     */
    private $columns = array();

    private $resolvedNameMethods = array();

    private $page = 1;
    private $pageSize = 10;

    private $total = 0;
    private $entities = array();


    public function type()
    {
        return 'listView';
    }

    public function runGetRequest()
    {
        $data = $this->context->request()->query->all();
        if ($data) {
            $this->set($data);
        }
        $data = $this->context->request()->request->all();
        if ($data) {
            $this->set($data);
        }

        $this->initColumns();
        $this->initQuery();
        return parent::runGetRequest();
    }

    /**
     * @return Query
     */
    abstract  protected function query();

    protected function initQuery()
    {
        $query = $this->query();
        $paramsEvent = new GetArrayEvent();

        $this->filter($query, $paramsEvent);
        $this->trigger('filter', $this, $query, $paramsEvent);

        $this->sort($query, $paramsEvent);
        $this->trigger('sort', $this, $query, $paramsEvent);

        $this->paging($query, $paramsEvent);
        $this->trigger('paging', $this, $query, $paramsEvent);

        $params = $paramsEvent->getArray();
        $this->total = $this->computeTotal($query, $params);
        $this->entities = $this->computeEntities($query, $params);
    }

    protected function computeTotal(Query $query, array $params)
    {
        return $query->total($params);
    }

    protected function computeEntities(Query $query, array $params)
    {
        return $query->all($params);
    }

    protected function initColumns()
    {
        $this->collectColumns($collection = new ColumnCollection($this));
        $this->trigger('collect', $this, $collection);
        $this->columns = $collection->all();
    }

    abstract protected function collectColumns(ColumnCollection $collection);

    /**
     * @return ColumnInterface[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    protected function resolveNameMethod($name)
    {
        if (!isset($this->resolvedNameMethods[$name])) {
            $this->resolvedNameMethods[$name] = implode(array_map('ucfirst', explode('_', strtr($name, array(
                                '[]' => '',
                                '][' => '_',
                                '[' => '_',
                                ']' => ''
                            )))));
        }
        return $this->resolvedNameMethods[$name];
    }

    public function formatColumn(ColumnInterface $column, Entity $entity, Html $td)
    {
        $m = $this->resolveNameMethod($column->getName());

        if (method_exists($this, $method = 'format'.$m.'ColumnHtml')) {
             $this->$method($td, $entity);
        }

        if (method_exists($this, $method = 'format' . $m . 'Column')) {
            $this->$method($entity, $td, $column);
        } else {
            $td->setContent($entity->get($column->getName()));
        }
    }

    protected function filter(Query $query, GetArrayEvent $event)
    {
    }

    protected function sort(Query $query, GetArrayEvent $event)
    {
        $request = $this->context->request();
        if (null !== ($sort = $request->query->get('sort'))) {
            $by = $request->query->get('by', 'desc');
            $query->order("{$sort} {$by}");
        }
    }

    protected function paging(Query $query, GetArrayEvent $event)
    {
        $count = $this->getPageSize();
        $offset = ($this->getPage() - 1) * $count;
        $query->limit($count, $offset);
    }

    public function generateSortingUrl($name)
    {
        return $this->context->generateCurrentUrl(array(
                'sort' => $name,
                'by' => $this->context->request()->query->get('sort') == $name ? 'asc' : 'desc'
            ));
    }

    public function generatePagingUrl($page)
    {
        return $this->context->generateCurrentUrl(array(
                'page' => $page
            ));
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = intval($page) ?: 1;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return Entity[]
     */
    public function getEntities()
    {
        return $this->entities;
    }
}