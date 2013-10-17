<?php

namespace Youngx\MVC\Yui;

use Youngx\MVC\Handler;
use Youngx\MVC\Bundle;
use Youngx\MVC\Application;
use Youngx\MVC\Request;
use Youngx\MVC\Templating\TemplateInterface;
use Youngx\MVC\Templating\EngineInterface;

class YuiEngine implements EngineInterface
{
    /**
     * @var Template[]
     */
    protected $templates = array();
    protected $bundles = array();
    protected $modules = array();
    protected $blocks = array();

    /**
     * @var Handler
     */
    protected $handler;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $name
     * @return Block
     */
    protected function getBlock($name)
    {
        if (!isset($this->blocks[$name])) {
            $this->blocks[$name] = new Block($this->handler, $name);
        }
        return $this->blocks[$name];
    }

    /**
     * @param $name
     * @param string | null $content
     * @return Block
     */
    public function block($name, $content = null)
    {
        $block = $this->getBlock($name);
        if (null !== $content) {
            $block->add($content);
        }
        return $block;
    }

    public function setApp(Application $app)
    {
        $this->app = $app;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function call($name, array $arguments = array())
    {
        $eventName = "kernel.templating.call.{$name}";

        if (!$this->handler->hasListeners($eventName)) {
            throw new \RuntimeException(sprintf('Templating call[%s] has not been registered.', $name));
        }

        array_unshift($arguments, $eventName);
        return call_user_func_array(array($this->handler, 'triggerForValue'), $arguments);
    }

    public function renderView($path, array $variables = array())
    {
        $this->handler->trigger('kernel.templating.before', $this);
        $content = $this->render($path, $variables);
        $this->handler->trigger('kernel.templating.after', $content, $this);

        return $content;
    }

    public function render($path, array $variables = array())
    {
        $template = new Template($this);
        $this->handler->triggerWithMenu("kernel.templating.process", $template);
        $content = $template->renderFile($this->parsePath($path), $variables);
        array_pop($this->bundles);
        array_pop($this->modules);
        return $content;
    }

    private function parsePath($template)
    {
        if (preg_match('/^([a-zA-Z0-9\.\/-]+)(@([a-zA-Z0-9]+)(:([a-zA-Z0-9]+))?)?$/', $template, $match)) {
            $path = $match[1];
            $bundle = isset($match[3]) ? $match[3] : null;
            $module = isset($match[5]) ? $match[5] : null;

            if (isset($bundle)) {
                $this->bundles[] = $this->app->getBundle($bundle);
                $this->modules[] = $module;
            } else {
                $this->bundles[] = $this->getBundle();
                $this->modules[] = $this->getModule();
            }

            $module = $this->getModule();
            $bundle = $this->getBundle()->getName();
            $uri = $module ? "app://templates/{$bundle}/{$module}/{$path}" : "app://templates/{$bundle}/{$path}";
            $file =  $this->app->locate($uri);
            if (is_file($file)) {
                return $file;
            }

            if ($module) {
                $file = $this->getBundle()->getModuleResourcesPath($module) . '/templates/' . $path;
            } else {
                $file = $this->getBundle()->getResourcesPath() . '/templates/' . $path;
            }
        } else {
            $file = $template;
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
            return $this->request->getBundle();
        }
        return $bundle;
    }

    public function getModule()
    {
        if (false === ($module = end($this->modules))) {
            return $this->request->getModule();
        }
        return $module;
    }
}