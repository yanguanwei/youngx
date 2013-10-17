<?php

namespace Youngx\MVC\Widget;

class SearchFormWidget extends BaseFormWidget
{
    protected $submit = array('#content' => '搜索');

    public function name()
    {
        return 'search-form';
    }

    protected function initFormHtml()
    {
        $formHtml = parent::initFormHtml();
        $formHtml->set('method', 'get');

        return $formHtml;
    }
}