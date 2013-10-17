<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Html;
use Youngx\MVC\Widget;

class WizardWidget extends Widget
{
    /**
     * @var Html
     */
    protected $headerHtml;
    /**
     * @var Html[]
     */
    protected $stepHtmls;
    protected $steps = array();
    protected $step = 0;

    protected $content;

    /**
     * @var Html
     */
    protected $actionsWrapHtml;
    protected $actions = array();

    /**
     * @var Html
     */
    protected $contentWrapHtml;

    /**
     * @var Html
     */
    protected $prevHtml;
    /**
     * @var Html
     */
    protected $nextHtml;
    protected $prev = array();
    protected $next = array();

    public function name()
    {
        return 'wizard';
    }

    protected function format($content)
    {
        $s = '';
        foreach ($this->stepHtmls as $li) {
            $s .= $li;
        }
        $headerHtml = $this->getHeaderHtml();
        $headerHtml->setContent($s);

        $actionsWrapHtml = $this->getActionsWrapHtml();
        $actionsWrapHtml->setContent($this->getPrevHtml() .' '. $this->getNextHtml());

        $contentWrapHtml = $this->getContentWrapHtml();
        $content = $this->content;

        if ($content && $content instanceof FormWidget) {
            $content->getActionsWrapHtml()->hide();
            $content->add('wizard-actions', $actionsWrapHtml, 1024);
            $contentWrapHtml->setContent($content);

        } else {
            $contentWrapHtml->setContent($content . $actionsWrapHtml)
                ->wrap($this->context->html('form'));
        }

        return $headerHtml . $contentWrapHtml;
    }

    /**
     * @return Html
     */
    public function getContentWrapHtml()
    {
        if (null === $this->contentWrapHtml) {
            $this->contentWrapHtml = $this->context->html('div');
        }
        return $this->contentWrapHtml;
    }

    /**
     * @return Html
     */
    public function getHeaderHtml()
    {
        if (null === $this->headerHtml) {
            $this->headerHtml = $this->context->html('ul');
        }
        return $this->headerHtml;
    }

    /**
     * @return Html
     */
    public function getActiveStepHtml()
    {
        $stepHtmls = $this->getStepHtmls();
        return $stepHtmls[$this->step];
    }

    /**
     * @return Html
     */
    public function getActionsWrapHtml()
    {
        if (null === $this->actionsWrapHtml) {
            $this->actionsWrapHtml = $this->context->html('div', $this->actions);
        }
        return $this->actionsWrapHtml;
    }

    /**
     * @return Html
     */
    public function getPrevHtml()
    {
        if (null === $this->prevHtml) {
            $prev = array_merge(array(
                    'href' => $this->step ? $this->context->generateCurrentUrl(array('step' => $this->step - 1)) :  '#',
                    'disabled' => $this->step == 0,
                    '#content' => '上一步'
                ), $this->prev);
            $this->prevHtml = $this->context->html('a', $prev);
        }
        return $this->prevHtml;
    }

    /**
     * @return Html
     */
    public function getNextHtml()
    {
        if (null === $this->nextHtml) {
            $next = array_merge(array(
                    '#content' => '下一步',
                ), $this->next);
            $this->nextHtml = $this->context->html('submit', $next);
        }
        return $this->nextHtml;
    }

    /**
     * @return Html[]
     */
    public function getStepHtmls()
    {
        if (null === $this->stepHtmls) {
            $i = 1;
            $this->stepHtmls = array();
            foreach ($this->steps as $title) {
                $this->stepHtmls[] = $li = $this->context->html('li');
                $li->append($this->context->html('span', array('#content' => $i++)), 'step');
                $li->append($this->context->html('span', array('#content' => $title)), 'title');
            }
        }
        return $this->stepHtmls;
    }

    /**
     * @param array $prev
     */
    public function setPrev(array $prev)
    {
        $this->prev = $prev;
    }

    /**
     * @param int $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @param array $steps
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;
    }

    /**
     * @param array $next
     */
    public function setNext(array $next)
    {
        $this->next = $next;
    }

    /**
     * @param array $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}