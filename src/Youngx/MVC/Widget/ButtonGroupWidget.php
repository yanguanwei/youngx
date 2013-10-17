<?php

namespace Youngx\MVC\Widget {

    use Youngx\MVC\Html;
    use Youngx\MVC\Widget;
    use Youngx\MVC\Widget\ButtonGroupWidget\Button;
    use Youngx\MVC\Widget\ButtonGroupWidget\ButtonGroup;
    use Youngx\Util\SortableArray;

    class ButtonGroupWidget extends Widget
    {
        /**
         * @var SortableArray
         */
        private $sortableArray;
        private $wrapHtml;
        private $buttonGroupWrapHtmls = array();
        private $buttonHtmls = array();

        public function name()
        {
            return 'button-group';
        }

        /**
         * @return Html[]
         */
        public function getButtonGroupWrapHtmls()
        {
            return $this->buttonGroupWrapHtmls;
        }

        /**
         * @return Html[]
         */
        public function getButtonHtmls()
        {
            return $this->buttonHtmls;
        }

        /**
         * @return Html
         */
        public function getWrapHtml()
        {
            if (null === $this->wrapHtml) {
                $this->wrapHtml = $this->context->html('div');
            }
            return $this->wrapHtml;
        }

        protected function setup()
        {
            if ($this->sortableArray) {
                foreach ($this->sortableArray->all() as $name => $button) {
                    $this->parseButtonGroup($this->getWrapHtml(), $name, $button);
                }
            }
        }

        protected function parseButtonGroup(Html $div, $name, $button)
        {
            $context = $this->context;
            if ($button instanceof Button) {
                $div->append(
                    $this->buttonHtmls[$name] = $context->html('button', array(
                            '#content' => $button->getLabel(),
                            'data-url' => $button->getUrl()
                        ))->set($button->getAttributes()),
                    $name
                );
            } else if ($button instanceof ButtonGroup) {
                $div->append(
                    $this->buttonGroupWrapHtmls[$name] = $wrap = $context->html('div'), $name
                );
                $wrap->append(
                    $this->buttonHtmls[$name] = $context->html('button', array(
                            '#content' => $button->getLabel(),
                        )),
                    'button'
                )->append(
                        $ul = $context->html('ul'),
                        'ul'
                    );
                foreach ($button->all() as $btn) {
                    $li = $context->html('li')->setContent(
                        $context->html('a', array(
                                'href' => $btn->getUrl(),
                                '#content' => $btn->getLabel(),
                                'data-url' => $btn->getUrl()
                            ))->set($btn->getAttributes())
                    );
                    $ul->append($li);
                }
            } else {
                $div->append($button, $name);
            }
        }

        protected function format($content)
        {
            $wrap = $this->getWrapHtml();
            if ($wrap) {
                $wrap->append($content);
            }

            return $wrap;
        }

        public function add($name, $button, $sort = 0)
        {
            $this->getSortableArray()->set($name, $button, $sort);

            return $this;
        }

        /**
         * @param $name
         * @param $label
         * @param $url
         * @param $sort
         * @return Button
         */
        public function addButton($name, $label, $url, $sort = 0)
        {
            $this->getSortableArray()->set($name, $button = new Button($label, $url), $sort);

            return $button;
        }

        /**
         * @param $name
         * @return ButtonGroup
         */
        public function getGroup($name)
        {
            if (!$this->getSortableArray()->has($name)) {
                $this->getSortableArray()->set($name, new ButtonGroup());
            }
            return $this->getSortableArray()->get($name);
        }

        public function remove($name)
        {
            $this->getSortableArray()->remove($name);

            return $this;
        }

        public function sort($name, $sort)
        {
            $this->getSortableArray()->sort($name, $sort);

            return $this;
        }

        /**
         * @return SortableArray
         */
        private function getSortableArray()
        {
            if (null === $this->sortableArray) {
                $this->sortableArray = new SortableArray();
            }
            return $this->sortableArray;
        }
    }
}

namespace Youngx\MVC\Widget\ButtonGroupWidget {

    use Youngx\Util\SortableArray;

    class Button
    {
        private $label;
        private $url;
        private $attributes = array();

        public function __construct($label, $url)
        {
            $this->setLabel($label);
            $this->setUrl($url);
        }

        public function setAttributes(array $attributes)
        {
            $this->attributes = $attributes;

            return $this;
        }

        public function addAttribute($key, $value)
        {
            $this->attributes[$key] = $value;

            return $this;
        }

        public function addAttributes(array $attributes)
        {
            $this->attributes = array_merge($this->attributes, $attributes);

            return $this;
        }

        /**
         * @return array
         */
        public function getAttributes()
        {
            return $this->attributes;
        }

        public function setLabel($label)
        {
            $this->label = $label;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLabel()
        {
            return $this->label;
        }

        public function setUrl($url)
        {
            $this->url = $url;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getUrl()
        {
            return $this->url;
        }
    }

    class ButtonGroup
    {
        private $label;
        private $buttons;

        /**
         * @return SortableArray
         */
        private function getButtonSortableArray()
        {
            if (null === $this->buttons) {
                $this->buttons = new SortableArray();
            }
            return $this->buttons;
        }

        /**
         * @param $name
         * @param $label
         * @param $url
         * @param int $sort
         * @return Button
         */
        public function add($name, $label, $url, $sort = 0)
        {
            $this->getButtonSortableArray()->set($name, $button = new Button($label, $url), $sort);

            return $button;
        }

        public function sort($name, $sort)
        {
            $this->getButtonSortableArray()->sort($name, $sort);

            return $this;
        }

        /**
         * @param $name
         * @return Button | null
         */
        public function get($name)
        {
            return $this->getButtonSortableArray()->get($name);
        }

        public function has($name)
        {
            return $this->getButtonSortableArray()->has($name);
        }

        public function remove($name)
        {
            $this->getButtonSortableArray()->remove($name);;

            return $this;
        }

        public function setLabel($label)
        {
            $this->label = $label;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLabel()
        {
            return $this->label;
        }

        /**
         * @return Button[]
         */
        public function all()
        {
            return $this->buttons ? $this->getButtonSortableArray()->all() : array();
        }
    }
}

