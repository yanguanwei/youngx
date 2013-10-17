<?php

namespace Youngx\Bundle\ChannelBundle\Controller;


use Youngx\Bundle\ChannelBundle\Entity\ChannelEntity;
use Youngx\MVC\Context;

class FormController
{

    public function addAction(Context $context)
    {
        return $this->execute($context, 'add');
    }

    public function editAction(Context $context, ChannelEntity $channel)
    {
        $this->channel = $channel;
        return $this->execute($context, 'edit');
    }
}