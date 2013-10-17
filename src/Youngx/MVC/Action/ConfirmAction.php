<?php

namespace Youngx\MVC\Action;

use Youngx\MVC\Action;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\MVC\RenderableResponse;
use Youngx\MVC\Widget\FormWidget;

abstract class ConfirmAction extends Action
{
    private $errorMessage;

    public function id()
    {
        return 'confirm';
    }

    protected function runRequest()
    {
        $this->initRequest();

        $this->errorMessage = $this->validate();

        if ($this->errorMessage === null) {
            $request = $this->context->request();
            if ($request->request->get('_confirm', 0)) {
                $event = new GetResponseEvent();
                $this->submit($event);
                if (!$event->hasResponse()) {
                    $event->setResponse($this->context->redirectResponse());
                }
                return $event->getResponse();
            } else {
                return $this->renderResponse();
            }
        } else {
            return $this->renderResponse();
        }
    }

    protected function render(RenderableResponse $response)
    {
        $returnUrl = $this->context->request()->get('returnUrl');

        $message = $this->context->html('message', array(
                '#type' => $this->errorMessage === null ? 'warning' : 'error',
                '#content' => $this->errorMessage === null ? $this->getMessage() : $this->errorMessage
            ));
        $formWidget = $this->context->widget('Form', array(
                '#skin' => 'vertical',
                'cancel' => array(
                    'href' => $returnUrl?: $this->getCancelledUrl()
                )
            ));
        if ($formWidget instanceof FormWidget) {
            $formWidget->add('confirm', $this->context->input('hidden', array(
                        'name' => '_confirm',
                        'value' => 1
                    )));
            $formWidget->getSubmitHtml()->set(array(
                    '#content' => '确认',
                    'disabled' => $this->errorMessage !== null
                ));
            $this->formatFormWidget($formWidget);
        }

        $response->addVariable('#subtitle', '<i>' . $this->context->request()->getMenu()->getLabel() . '</i> 确认');

        $response->setContent($message . $formWidget);
    }

    protected function formatFormWidget(FormWidget $widget)
    {
    }

    /**
     * @return string
     */
    abstract protected function getMessage();

    /**
     * @return string
     */
    abstract protected function getCancelledUrl();

    /**
     * @return string
     */
    protected function validate()
    {
    }

    abstract  protected function submit(GetResponseEvent $event);
}