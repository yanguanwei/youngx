<?php

namespace Youngx\Kernel\Yui;

use Youngx\Kernel\Form as Model;
use Youngx\Kernel\Form\Input;
use Youngx\UI\Html;

class Form
{
    /**
     * @var \Youngx\UI\HtmlTag
     */
    protected $form;

    /**
     * @var \Youngx\Kernel\Form
     */
    protected $model;

    protected $data;

    public function __construct(Model $model, array $attributes = array())
    {
        $this->model = $model;
        $this->form = Html::element('form', array_merge($model->attributes(), $attributes));
    }

    public function end()
    {
        $this->form->end();
    }

    /**
     * string Input@Bundle
     * array(
     *  0 => Input@Bundle
     *  'label' => string | array
     *  'name' =>
     *  'input' => 'text'
     * )
     * @param $config
     */
    public function field($config)
    {
        $field = '';

        if (is_array($config)) {
            if (isset($config[0])) {

            } else {
                if (isset($config['label'])) {
                    $field .= $this->resolveLabel($config);
                }

                if (isset($config['input'])) {
                    if (false === strpos($config['input'], '@')) {

                    }
                }
            }
        }
    }

    protected function resolveLabel(array $config)
    {
        $label = Html::element('label', $config['label']);
        if (isset($config['name'])) {
            $label->set('for', $config['name']);
        }
        return $label;
    }

    protected function resolveTextInput(array $config, $type = 'text')
    {
        if (!isset($config['attributes'])) {
            $attributes = $config['attributes'];
        } else {
            $attributes = array();
        }

        if (isset($config['name'])) {
            $attributes[0] = $config['name'];
            $attributes['value'] = $this->model->get($config['name']);
        }

        return Html::$type($attributes);
    }
}