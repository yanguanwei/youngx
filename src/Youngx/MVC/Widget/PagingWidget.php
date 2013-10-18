<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Context;
use Youngx\MVC\Widget;
use Youngx\MVC\Html;

class PagingWidget extends Widget
{
    private $pageSize = 10;
    private $total = 0;
    private $page = 1;
    private $urlGenerator;
    private $currentClass = 'active';
    private $disabledClass = 'disabled';
    private $prev = '&lt;';
    private $next = '&gt;';
    private $nextClass = 'next';
    private $prevClass = 'prev';

    private $lastPage;
    private $prevPage;
    private $nextPage;

    /**
     * @var Html
     */
    private $wrapHtml;
    /**
     * @var Html
     */
    private $ulHtml;

    public function name()
    {
        return 'paging';
    }

    /**
     * @return Html
     */
    public function getWrapHtml()
    {
        if (null === $this->wrapHtml) {
            $this->wrapHtml = $this->context->html('div');
        }
        return $this->wrapHtml;
    }

    /**
     * @return Html
     */
    public function getUlHtml()
    {
        if (null === $this->ulHtml) {
            $this->ulHtml = $this->context->html('ul');
        }
        return $this->ulHtml;
    }

    protected function run()
    {
        $context = $this->context;

        $this->lastPage = ceil($this->total / $this->pageSize);
        $this->page = min($this->lastPage, $this->page);
        $this->prevPage = $this->page - 1;
        $this->nextPage = ($this->page == $this->lastPage ? 0 : $this->page + 1);

        $pageLength = 8;
        if ($this->lastPage < 1) {
            return false;
        }

        $ul = $this->getUlHtml();

        $ul->append(
            $li = $context->html(
                'li',
                array(
                    'class' => $this->prevClass,
                    '#content' => $context->html('a', array('href' => $this->prevPage ? $this->generateUrl($this->prevPage, $context) : '#', '#content' => $this->prev))
                )
            ));

        if (!$this->prevPage) {
            $li->addClass($this->disabledClass);
        }

        $startPage = (($this->page-1)>4) ? $this->page-4 : 1;
        $endPage = (($startPage + $pageLength)>$this->lastPage) ? $this->lastPage : ($startPage + $pageLength);

        if ($this->lastPage == $endPage){
            $startPage = (($this->lastPage - $pageLength)>0) ? $this->lastPage - $pageLength : 1;
        }

        if ($startPage > 1) {
            $ul->append(
                $context->html('li', array(
                        '#content' => $context->html('a', array('href' => $this->generateUrl('1', $context), '#content' => 1)) . '...'
                    ))
            );
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $ul->append(
                $context->html(
                    'li',
                    array(
                        'class' => $i == $this->page ? $this->currentClass : '',
                        '#content' => $context->html('a', array('href' => $this->generateUrl($i, $context), '#content' => $i))
                    )
                )
            );
        }

        if ($endPage < $this->lastPage) {
            $ul->append($context->html(
                    'li',
                    array(
                        '#content' => '.......' . $context->html('a', array('href' => $this->generateUrl($this->lastPage, $context), '#content' => $this->lastPage))
                    )
                ));
        }

        $ul->append($li = $context->html(
                'li',
                array(
                    'class' => $this->nextClass,
                    '#content' => $context->html('a', array(
                            'href' => $this->nextPage ? $this->generateUrl($this->nextPage, $context) : '#',
                            '#content' =>  $this->next
                        ))
                )
            ));

        if (!$this->nextPage) {
            $li->addClass($this->disabledClass);
        }
    }

    protected function format($content)
    {
        return $this->getWrapHtml()->setContent($this->getUlHtml() . $content);
    }

    protected function generateUrl($page, Context $context)
    {
        if ($this->urlGenerator) {
            return call_user_func($this->urlGenerator, $page, $context);
        } else {
            return $this->context->generateCurrentUrl(array(
                    'page' => $page
                ));
        }
    }

    /**
     * @param string $currentClass
     */
    public function setCurrentClass($currentClass)
    {
        $this->currentClass = $currentClass;
    }

    /**
     * @param string $disabledClass
     */
    public function setDisabledClass($disabledClass)
    {
        $this->disabledClass = $disabledClass;
    }

    /**
     * @param string $next
     */
    public function setNext($next)
    {
        $this->next = $next;
    }

    /**
     * @param string $nextClass
     */
    public function setNextClass($nextClass)
    {
        $this->nextClass = $nextClass;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = intval($page);
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = intval($pageSize);
    }

    /**
     * @param string $prev
     */
    public function setPrev($prev)
    {
        $this->prev = $prev;
    }

    /**
     * @param string $prevClass
     */
    public function setPrevClass($prevClass)
    {
        $this->prevClass = $prevClass;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = intval($total);
    }

    /**
     * @param mixed $urlGenerator
     */
    public function setUrlGenerator($urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
}