<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\Response;
use Youngx\MVC\Context;
use Youngx\Util\PropertyAccess;

abstract class Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PropertyAccess
     */
    private $propertyAccess;

    /**
     * @var Action
     */
    private $parent;

    public function __construct(Context $context, Action $parent = null)
    {
        $this->context = $context;
        $this->parent = $parent;

        $this->init();

        $this->trigger('init', $this);
    }

    protected function trigger($name)
    {
        $type = $this->type();
        $id = $this->id();

        $args = func_get_args();
        $args[0] = array(
            "kernel.{$type}.{$name}#{$id}",
            "kernel.{$type}.{$name}"
        );

        return call_user_func_array(array($this->context->handler(), 'trigger'), $args);
    }

    /**
     * @return PropertyAccess
     */
    public function propertyAccess()
    {
        if (null === $this->propertyAccess) {
            $this->propertyAccess = new PropertyAccess($this);
        }
        return $this->propertyAccess;
    }

    abstract public function id();

    public function type()
    {
        return 'action';
    }

    public function get($key)
    {
        return $this->propertyAccess()->get($key);
    }

    public function set($key, $value = null)
    {
        $this->propertyAccess()->set($key, $value);

        return $this;
    }

    protected function init()
    {
    }

    protected function initRequest()
    {
    }

    final public function run()
    {
        if ($this->context->request()->isMethod('POST')) {
            return $this->runPostRequest();
        } else {
            return $this->runGetRequest();
        }
    }

    /**
     * @return Response | null
     */
    final public function runPostRequest()
    {
        $data = $this->context->request()->query->all();
        if ($data) {
            $this->set($data);
        }
        $data = $this->context->request()->request->all();
        if ($data) {
            $this->set($data);
        }
        $data = $this->context->request()->files->all();
        if ($data) {
            $this->set($data);
        }

        $this->initRequest();

        return $this->doPostRequest();
    }

    /**
     * @return RenderableResponse
     */
    final public function runGetRequest()
    {
        $data = $this->context->request()->query->all();
        if ($data) {
            $this->set($data);
        }

        $this->initRequest();

        return $this->doGetRequest();
    }

    /**
     * @param RenderableResponse $response
     * @return RenderableResponse
     */
    final protected function renderResponse(RenderableResponse $response = null)
    {
        $response = $response ?: $this->context->renderableResponse();
        $this->render($response);
        $this->context->handler()->trigger("kernel.{$this->type()}.render#{$this->id()}", $response, $this);
        return $response;
    }

    /**
     * @return RenderableResponse
     */
    protected function doPostRequest()
    {
        return $this->renderResponse();
    }

    /**
     * @return RenderableResponse
     */
    protected function doGetRequest()
    {
        return $this->renderResponse();
    }

    protected function render(RenderableResponse $response)
    {
    }
}