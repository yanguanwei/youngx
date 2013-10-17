<?php

namespace Youngx\Bundle\NewsBundle\Controller;

use Youngx\Bundle\ChannelBundle\Entity\ChannelEntity;
use Youngx\MVC\Context;

class AdminController
{
    public function addAction(Context $context, ChannelEntity $channel)
    {
        return $context->formResponse('Admin@News', array(
                'channel' => $channel
            ));
    }
}