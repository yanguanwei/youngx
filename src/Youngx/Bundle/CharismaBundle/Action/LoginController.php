<?php

namespace Youngx\Bundle\CharismaBundle\Controller;

use App;
use Youngx\Bundle\UserBundle\Form\LoginForm;

class LoginController extends LoginForm
{
    public function indexAction()
    {
        return $this->execute('admin');
    }

    protected function render()
    {
        return App::render('login.html.yui@Charisma', array(
                'form' => $this
            ));
    }
}
