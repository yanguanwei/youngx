<?php

namespace Youngx\MVC\Html;

use Youngx\MVC\Context;
use Youngx\MVC\Html;

class SelectHtml extends Html
{
    private $empty;
    private $options;
    private $hierarchy;
    private $root = 0;

    public function __construct(Context $context, array $attributes = array())
    {
        parent::__construct($context, 'select', $attributes);
    }

    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    public function setHierarchy(array $hierarchy)
    {
        $this->hierarchy = $hierarchy;

        return $this;
    }

    public function setEmpty($empty)
    {
        $this->empty = $empty;

        return $this;
    }

    protected function format()
    {
        if ($this->options) {
            if ($this->hierarchy) {
                $this->options = $this->parseHierarchicalOptions($this->hierarchy, $this->options, $this->root);
            }

            if ($this->empty) {
                $this->options = ((array) $this->empty) + $this->options;
            }

            $this->setContent($this->parseSelectOptions($this->options, (array) $this->getValue()));
        }

        if ($this->get('multiple', false)) {
            if ($this->has('name')) {
                $this->set('name', $this->get('name') . '[]');
            }
        }
    }

    private function parseSelectOptions(array $options, array $value)
    {
        $s = '';
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                $s .= '<optgroup label="'.$k.'">' . $this->parseSelectOptions($v, $value) . '</optgroup>';
            } else {
                $s .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    $k,
                    in_array($k, $value) ? ' selected="selected"' : '',
                    $v
                );
            }
        }
        return $s;
    }

    private function parseHierarchicalOptions(array $hierarchy, array $rawData, $root)
    {
        $data = $options = $flags = array();

        foreach ($hierarchy as $id => $parent) {
            $data[$parent][$id] = $rawData[$id];
        }

        $this->parseHierarchicalOptionPrefix($data, $options, $flags, $this->hierarchicalOptionPrefixes(), $root, 0);

        return $options;
    }

    private function parseHierarchicalOptionPrefix(array &$data, array &$options, array &$flags, array $prefixes, $parent, $layer)
    {
        if (isset($data[$parent])) {
            foreach ($data[$parent] as $id => $label) {
                unset($data[$parent][$id]);
                $flags[$layer] = empty($data[$parent]) ? false : true;

                $prefix = '';
                if ($layer > 0) {
                    for ($i = 0; $i <= $layer; $i++) {
                        if ($i == $layer) {
                            if ($flags[$i]) {
                                $prefix .= $prefixes['next'];
                            } else {
                                $prefix .= $prefixes['end'];
                            }
                        } else {
                            if ($flags[$i]) {
                                $prefix  .= $prefixes['nextSpace'];
                            } else {
                                $prefix .= $prefixes['space'];
                            }
                        }
                    }
                }

                $prefix .= empty($data[$parent]) ? $prefixes['noChildren'] : $prefixes['hasChildren'];

                $options[$id] = $prefix . $label;

                $this->parseHierarchicalOptionPrefix($data, $options, $flags, $prefixes, $id, $layer+1);
            }
        }
    }

    protected function hierarchicalOptionPrefixes() {
        return array(
            'hasChildren'	=> '',
            'noChildren'	=> '',
            'next'			=> '&nbsp;├&nbsp;',
            'end'			=> '&nbsp;└&nbsp;',
            'nextSpace'		=> '&nbsp;│',
            'space'			=> '&nbsp;&nbsp;&nbsp;'
        );
    }
}