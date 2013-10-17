<?php

namespace Youngx\MVC\Widget;

use Youngx\Database\Entity;
use Youngx\MVC\ListView\ColumnInterface;
use Youngx\MVC\Widget;
use Youngx\MVC\Html;
use Youngx\MVC\Widget\PagingWidget;
use Youngx\MVC\Table;

class TableWidget extends Widget
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var ColumnInterface[]
     */
    protected $columns = array();
    /**
     * @var Entity[]
     */
    protected $entities = array();
    /**
     * @var Html
     */
    protected $tableHtml;

    /**
     * @var PagingWidget
     */
    protected $pagingWidget;

    protected $batch;

    /**
     * @var Html
     */
    protected $checkboxThHtml;

    /**
     * @var Html[]
     */
    protected $checkboxTdHtmls;

    protected $buttonGroupWidget;

    protected $tdHtmlRows;

    /**
     * @var Html
     */
    protected $actionsWrapHtml;

    public function name()
    {
        return 'table';
    }

    protected function init()
    {
        if ($this->table) {
            $this->columns = $this->table->getColumns();
            $this->entities = $this->table->getEntities();
            $this->batch = $this->table->getBatchName();
        }
    }

    public function getCheckboxThHtml()
    {
        if (!$this->batch) {
            return ;
        }

        if (null === $this->checkboxThHtml) {
            $this->checkboxThHtml = $this->context->html('th')->append(
                $this->context->html('checkbox'), 'checkbox'
            );

            $this->registerBatchCheckAllScriptCode();
        }

        return $this->checkboxThHtml;
    }

    /**
     * @return ButtonGroupWidget | null
     */
    public function getButtonGroupWidget()
    {
        if (null !== $this->buttonGroupWidget) {
            return $this->buttonGroupWidget;
        }

        if (!$this->batch) {
            return ;
        }

        $this->buttonGroupWidget = $this->context->widget('ButtonGroup');
        if ($this->table && method_exists($this->table, 'renderButtonGroupWidget')) {
            $this->table->renderButtonGroupWidget($this->buttonGroupWidget);
        }

        return $this->buttonGroupWidget;
    }

    /**
     * @return PagingWidget | null
     */
    public function getPagingWidget()
    {
        if (null !== $this->pagingWidget) {
            return $this->pagingWidget;
        }

        if (!$this->table || $this->table->getTotal() <= $this->table->getPageSize()) {
            return ;
        }

        $this->pagingWidget = $this->context->widget('Paging', array(
                '#total' => $this->table->getTotal(),
                '#pageSize' => $this->table->getPageSize(),
                '#page' => $this->table->getPage(),
                '#urlGenerator' => array($this->table, 'generatePagingUrl'),
            ));

        return $this->pagingWidget;
    }

    protected function registerBatchCheckAllScriptCode()
    {
        $this->getTableHtml()->wrap($form = $this->context->html('form', array(
                    'method' => 'post'
                )));

        $buttonGroup = $this->getButtonGroupWidget();

        $code = <<<code
$('#{$this->tableHtml->getId()} th input:checkbox').on('click' , function(){
					var that = this;
					$(this).closest('table').find('tr > td:first-child input:checkbox')
					.each(function(){
						this.checked = that.checked;
						$(this).closest('tr').toggleClass('selected');
					});
				});

$('#{$buttonGroup->getWrapHtml()->getId()}').find('button[data-url], a').click(function() {
    var form = $('#{$form->getId()}');
    var returnUrl = "{$this->context->request()->getUri()}";
    var url = $(this).attr('data-url');
    url += (url.indexOf('?') == -1 ? '?' : '&') + 'returnUrl=' + returnUrl;
    form.attr('action', url);
    var checkboxes = form.find('input[name="{$this->batch}[]"]:checked');
    if (checkboxes.length > 0) {
        form.submit();
    } else {
        alert('没有选择可操作的项！');
    }
    return false;
});
code;

        $this->context->assets()->registerScriptCode($this->tableHtml->getId(), $code);
    }

    /**
     * @return array
     */
    public function getTdHtmlRows()
    {
        if (null === $this->tdHtmlRows) {
            $tdHtmlRows = array();
            $checkboxTdHtmls = $this->getCheckboxTdHtmls();
            foreach ($this->entities as $i => $entity) {
                $row = array();
                if ($checkboxTdHtmls && isset($checkboxTdHtmls[$i])) {
                    $row[$this->batch] = $checkboxTdHtmls[$i];
                }
                foreach ($this->columns as $column) {
                    $td = $this->context->html('td');
                    $column->format($this->context, $entity, $td);
                    $row[$column->getName()] = (string) $td;
                }
                $tdHtmlRows[$i] = $row;
            };
            $this->tdHtmlRows = $tdHtmlRows;
        }
        return $this->tdHtmlRows;
    }

    protected function renderTHeadHtml()
    {
        $this->getTableHtml()->find('thead')->setContent($tr = $this->context->html('tr'));
        $headings = $this->getCheckboxThHtml();
        foreach ($this->columns as $column) {
            $th = $this->context->html('th', array(
                    '#content' => $column->getLabel()
                ));
            if ($this->table) {
                $this->table->formatColumnHeading($column, $th);
            }
            $headings .= $th;
        }
        $tr->setContent($headings);
    }

    protected function renderTBodyHtml()
    {
        $i = 1;
        $tbody = '';
        foreach ($this->getTdHtmlRows() as $row) {
            $tbody .=  $this->context->html('tr', array(
                    'class' => ($i++) % 2 === 1 ? 'odd' : 'even'
                ))->setContent(implode($row));
        }
        $this->getTableHtml()->find('tbody')->setContent($tbody);
    }

    protected function format($content)
    {
        $this->renderTHeadHtml();
        $this->renderTBodyHtml();

        if ($content) {
            $this->getTableHtml()->find('tbody')->append($content);
        }

        return $this->getTableHtml() . "\n" . $this->getActionsWrapHtml();
    }

    /**
     * @return Html
     */
    public function getTableHtml()
    {
        if (null === $this->tableHtml) {
            $this->tableHtml = $this->context->html('table')
                ->append($this->context->html('thead'), 'thead')
                ->append($this->context->html('tbody'), 'tbody');
        }

        return $this->tableHtml;
    }

    /**
     * @return Html
     */
    public function getActionsWrapHtml()
    {
        if (null === $this->actionsWrapHtml) {
            $this->actionsWrapHtml = $this->context->html('div')
                ->append($this->getButtonGroupWidget(), 'button-group')
                ->append($this->getPagingWidget(), 'paging');
        }

        return $this->actionsWrapHtml;
    }

    /**
     * @param array $columns
     */
    protected function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param Table $table
     */
    protected function setTable(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @return Html[]
     */
    public function getCheckboxTdHtmls()
    {
        if (null !== $this->checkboxTdHtmls) {
            return $this->checkboxTdHtmls;
        }

        if (!$this->batch) {
            return $this->checkboxTdHtmls = array();
        }

        $this->checkboxTdHtmls = array();
        foreach ($this->entities as $i => $entity) {
            $this->checkboxTdHtmls[$i] = $this->checkboxTdHtmls[$entity->identifier()] = $this->context->html('td')->append(
                $this->context->html('checkbox', array(
                        'name' => $this->batch . '[]',
                        'value' => $entity->get($this->batch)
                    )), 'checkbox'
            );
        }
        return $this->checkboxTdHtmls;
    }

    /**
     * @param mixed $batch
     */
    protected function setBatch($batch)
    {
        $this->batch = $batch;
    }

    /**
     * @return mixed
     */
    public function getBatch()
    {
        return $this->batch;
    }

    protected function setEntities(array $entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }
}