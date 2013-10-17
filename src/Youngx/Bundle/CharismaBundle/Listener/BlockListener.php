<?php

namespace Youngx\Bundle\CharismaBundle\Listener;

use Youngx\Kernel\Container  as Y;
use Youngx\Kernel\GetValue;
use Youngx\Kernel\GetValue\GetArray;
use Youngx\Kernel\GetValue\GetGroupArray;
use Youngx\Kernel\Handler\ListenerRegistration;

class AdminBlockListener implements ListenerRegistration
{
    public function admin_header(GetArray $array)
    {
        $array->set('menu', $this->admin_header_menu());
    }

    protected function admin_header_menu()
    {
        $s = '<div class="top-nav nav-collapse"><ul class="nav">';
        Y::trigger('kernel.web.menu', $array = new GetArray(), '/admin');
        foreach ($array->all() as $attributes) {
            $li = Y::element('li', $a = Y::element('a', $attributes));
            if ($a->getConfig('active', false)) {
                $li->addClass('active');
            }
            $s .= $li;
        }

        $s .= '<li><form class="navbar-search pull-left"><input placeholder="Search" class="search-query span2" name="query" type="text"></form></li>';
        $s .= '</ul></div>';

        return $s;
    }

    public function admin_sidebar(GetArray $array)
    {
        $menu = $this->admin_sidebar_menu();
        if ($menu) {
            $array->set('menu', $menu);
        }
    }

    protected function admin_sidebar_menu()
    {
        Y::triggerRoute("admin.sidebar.menu", $group = new GetGroupArray());
        if ($group->has()) {
            $s = '<div class="span2 main-menu-span"><div class="well nav-collapse sidebar-nav"><ul class="nav nav-tabs nav-stacked main-menu">';
            foreach ($group->all() as $key => $array) {
                $s .= sprintf('<li class="nav-header hidden-tablet">%s</li>', $group->getGroup($key));
                $s .= $this->admin_sidebar_menu_list($array);
            }
            $s .= '</ul></div></div>';
            return $s;
        }
    }

    protected function admin_sidebar_menu_list(GetArray $array)
    {
        $list = array();

        foreach ($array->all() as $attributes) {

            $li = Y::element('li', $a = Y::element('a', $attributes));
            if ($a->hasConfig('active', false)) {
                $li->addClass('active');
            }

            $a->setValue(Y::element('span', $a->getValue(), array('class' => 'hidden-tablet')));

            $list[] = $li;
        }

        return implode('', $list);
    }

    public function admin_content(GetArray $array)
    {
        $array->set('breadcrumb', $this->admin_content_breadcrumb(), -10);
    }

    protected function admin_content_breadcrumb()
    {
        $s = '';

        $s .= '<div><ul class="breadcrumb">';
        Y::trigger('kernel.web.breadcrumb', $breadcrumbs = new GetArray());

        $breadcrumbs = $breadcrumbs->all();

        if ($breadcrumbs) {
            $keys = array_keys($breadcrumbs);
            $last = end($keys);
            foreach ($breadcrumbs as $key => $breadcrumb) {
                $li = Y::element('li', $a = Y::element('a', $breadcrumb));
                if ($key != $last) {
                    $li->append('<span class="divider">/</span>');
                }
                $s .= $li;
            }
        }
        $s .= '</ul></div>';

        return $s;
    }

    public static function registerListeners()
    {
        return array(
            'kernel.block.admin.body.header' => 'admin_header',
            'kernel.block.admin.body.sidebar' => 'admin_sidebar',
            'kernel.block.admin.body.content' => 'admin_content'
        );
    }
}