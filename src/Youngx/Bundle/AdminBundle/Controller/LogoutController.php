<?php

namespace Youngx\Bundle\AdminBundle\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Youngx\Bundle\FrameworkBundle\App;

class LogoutAction
{
    public function run()
    {
        App::logout();

        $returnUrl = App::request()->query->get('returnUrl');

        return RedirectResponse::create($returnUrl ? $returnUrl : App::generateUrl('admin_login'));
    }
}