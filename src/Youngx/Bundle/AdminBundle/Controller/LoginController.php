<?php

namespace Youngx\Bundle\AdminBundle\Action;

use Y;
use Symfony\Component\HttpFoundation\Response;
use Youngx\Bundle\UserBundle\Form\LoginForm;

class LoginAction extends LoginForm
{
    public function run()
    {
        return $this->execute('admin');
    }

    protected function render()
    {
        if ($this->hasError()) {
            Y::session()->getFlashBag()->add('error', $this->getError(0));
        }

        return new Response(Y::render('login.html.yui@Charisma', array(
            'form' => $this
        )));
    }
}
