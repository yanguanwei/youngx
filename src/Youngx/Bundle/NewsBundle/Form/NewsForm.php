<?php

namespace Youngx\Bundle\NewsBundle\Form;

use Youngx\Bundle\ArchiveBundle\Form\ArchiveForm;
use Youngx\MVC\Context;
use Youngx\MVC\Event\GetRenderableEvent;

class AdminForm extends ArchiveForm
{
    protected function render(Context $context, GetRenderableEvent $event)
    {
        $event->setFile('admin/form.html.yui@News')
            ->addVariable('form', $this);
    }

    public function id()
    {
        return 'news-admin';
    }
}