<?php

namespace Youngx\MVC\Widget;

use Youngx\MVC\Widget;
use Youngx\MVC\Html;

class ConfirmWidget extends Widget
{
    /**
     * @var Html
     */
    private $wrapHtml;
    /**
     * @var Html
     */
    private $buttonWrapHtml;
    /**
     * @var Html
     */
    private $messageHtml;

    /**
     * @var FormWidget
     */
    private $formWidget;

    /**
     * @var \Youngx\Database\Entity[]
     */
    private $entities;

    private $message = array();

    private $buttonWrap = array();

    private $submit = array(
        'value' => '确认'
    );
    private $cancel = array(
        'value' => '取消'
    );

    public function name()
    {
        return 'confirm';
    }

    protected function init()
    {
        $this->wrapHtml = $this->context->html('div');
        $this->formWidget = $this->context->widget('Form', array(
                '#submit' => $this->submit,
                '#cancel' => $this->cancel
            ));

        $this->messageHtml = $this->context->html('p', $this->message);
        $this->buttonWrapHtml = $this->context->html('p', $this->buttonWrap)
            ->setContent($this->formWidget);

        if ($this->entities) {
            foreach ($this->entities as $entity) {
                $this->formWidget->add(
                    $this->context->input('hidden', array(
                            'value' => $entity->identifier(),
                            'name' => $entity->primaryKey().'[]'
                        ))
                );
            }
        }
    }

    protected function run()
    {
        $this->wrapHtml->setContent(
            $this->messageHtml . $this->buttonWrapHtml
        );

        echo $this->wrapHtml;
    }

    /**
     * @return \Youngx\MVC\Html
     */
    public function getButtonWrapHtml()
    {
        return $this->buttonWrapHtml;
    }

    /**
     * @param array $buttonWrap
     */
    public function setButtonWrap(array $buttonWrap)
    {
        $this->buttonWrap = $buttonWrap;
    }

    /**
     * @param array $cancel
     */
    public function setCancel(array $cancel)
    {
        $this->cancel = array_merge($this->cancel, $cancel);
    }

    /**
     * @param array | string $message
     */
    public function setMessage($message)
    {
        $this->message = is_array($message) ? $message : array('#content' => $message);
    }

    /**
     * @return \Youngx\MVC\Html
     */
    public function getMessageHtml()
    {
        return $this->messageHtml;
    }

    /**
     * @param array $submit
     */
    public function setSubmit(array $submit)
    {
        $this->submit = array_merge($this->submit, $submit);
    }

    /**
     * @return \Youngx\MVC\Html
     */
    public function getWrapHtml()
    {
        return $this->wrapHtml;
    }

    /**
     * @return \Youngx\MVC\Widget\FormWidget
     */
    public function getFormWidget()
    {
        return $this->formWidget;
    }

    /**
     * @param \Youngx\Database\Entity[] $entities
     */
    public function setEntities(array $entities)
    {
        $this->entities = $entities;
    }
}