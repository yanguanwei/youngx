<?php

namespace Youngx\Kernel;

use Youngx\Kernel\Container as Y;

class YuiEngine
{
    /**
     * @var Template[]
     */
    protected $templates = array();
    protected $bundles = array();
    /**
     * @var Extend
     */
    protected $extend;

    public function __construct()
    {
        $this->extend = new Extend($this, 'templating');
    }

    public function call($name, array $arguments = array())
    {
        if (!$this->extend->hasMethod($name)) {
            throw new \RuntimeException(sprintf('There is no extended method[%s].', $name));
        }
        return $this->extend->call($name, $arguments);
    }

    public function render($path, array $variables = array(), Template $template = null)
    {
        if (empty($this->templates)) {
            Y::handler()->trigger('templating.before', $this);
        }

        $file = $this->parsePath($path);

        if (null === $template)
            $template = new Template($this);

        $this->templates[] = $template;

        $content = $template->renderFile($file, $variables);

        array_pop($this->bundles);
        array_pop($this->templates);

        if (empty($this->templates)) {
            Y::handler()->trigger('templating.after', $content, $this);
        }

        return $content;
    }

    private function parsePath($template)
    {
        if (false !== ($p = strpos($template, '@'))) {
            $this->bundles[] = Y::bundle(substr($template, $p + 1));
            $path = substr($template, 0, $p);
        } else {
            $this->bundles[] = $this->getBundle();
            $path = $template;
        }

        $module = null;
        if (false !== ($p = strpos($path, ':'))) {
            $module = substr($path, 0, $p);
            $path = substr($path, $p + 1);
        }

        $bundle = $this->getBundle()->getName();
        $uri = $module ? "app://templates/{$bundle}/{$module}/{$path}" : "app://templates/{$bundle}/{$path}";
        $file =  Y::locate($uri);
        if (is_file($file)) {
            return $file;
        }

        if ($module) {
            $file = $this->getBundle()->getModuleResourcesPath($module) . '/templates/' . $path;
        } else {
            $file = $this->getBundle()->getResourcesPath() . '/templates/' . $path;
        }
        if (is_file($file)) {
            return $file;
        }

        throw new \RuntimeException(sprintf('Template[%s] is not a valid path.', $template));
    }

    /**
     *
     * @throws \RuntimeException
     * @return Template
     */
    public function getTemplate()
    {
        if (false === ($template = end($this->templates))) {
            throw new \RuntimeException();
        }
        return $template;
    }

    /**
     * @throws \RuntimeException
     * @return Bundle
     */
    public function getBundle()
    {
        if (false === ($bundle = end($this->bundles))) {
            return Y::request()->getBundle();
        }
        return $bundle;
    }
}