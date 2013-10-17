<?php

namespace Youngx\Bundle\AlipayBundle;

use Youngx\Kernel\Bundle;
use Youngx\Kernel\Handler\ListenerRegistration;
use Youngx\Kernel\MenuCollection;

class AlipayBundle extends Bundle implements ListenerRegistration
{

    public function registerMenu(MenuCollection $collection)
    {
        $collection->add('user.login.alipay', '/user/login/alipay', '支付宝快捷登录', 'Login@Alipay');
    }

    public static function registerListeners()
    {
        return array(
            'kernel.menu.register' => 'registerMenu'
        );
    }
}