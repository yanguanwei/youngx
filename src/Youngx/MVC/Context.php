<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Youngx\Database\Query;
use Youngx\DI\Container;
use Youngx\EventHandler\Event\GetValueEvent;
use Youngx\MVC\Event\GetRenderableResponseEvent;
use Youngx\MVC\Html;
use Youngx\MVC\User\Identity;
use Youngx\MVC\Templating\BlockInterface;
use Youngx\MVC\Input\InputInterface;

class Context
{
    /**
     * @var Container
     */
    protected $container;
    protected $app;

    public function __construct(Application $app, Container $container)
    {
        $this->app = $app;
        $this->container = $container;
    }

    /**
     * @param $class
     * @param array $data
     * @throws \Exception
     * @return Action
     */
    public function action($class, array $data = array())
    {
        if (strpos($class, '@') !== false) {
            $class = $this->app->resolveClass($class);
        }

        $className = '\\' . $class;
        $action = new $className($this);

        if ($action instanceof Action) {
            if ($data) {
                $action->set($data);
            }
            return $action;
        } else {
            throw new \Exception(sprintf(
                'Action[%s] must be instance of Youngx\MVC\Action, %s given',
                $class,
                is_object($action) ? get_class($action) : gettype($action)
            ));
        }
    }

    /**
     * @param $class
     * @param array $data
     * @throws \Exception
     * @return Response
     */
    public function actionResponse($class, array $data = array())
    {
        return $this->action($class, $data)->run();
    }

    /**
     * @return Application
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * @return \Youngx\MVC\Assets
     */
    public function assets()
    {
        return $this->container->get('assets');
    }

    public function assetUrl($path)
    {
        return $this->assets()->url($path);
    }

    /**
     * @param $name
     * @param null $content
     * @return BlockInterface
     */
    public function block($name, $content = null)
    {
        return $this->templating()->block($name, $content);
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider;
     */
    public function cache()
    {
        return $this->get('cache');
    }

    public function config($path, $default = null)
    {
        $config = $this->app->getConfiguration();
        foreach (explode(':', $path) as $key) {
            if (is_array($config) && isset($config[$key])) {
                $config = $config[$key];
            } else {
                $config = $default;
                break;
            }
        }
        return $config;
    }

    /**
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * @return \Youngx\Database\Connection
     */
    public function db()
    {
        return $this->container->get('database');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    public function flash()
    {
        return $this->session()->getFlashBag();
    }

    public function generateUrl($name, array $parameters = array(), $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->router()->generate($name, $parameters, $referenceType);
    }

    public function generateUrlWithCurrent($name, $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        $attributes = $this->request()->attributes->get('_route_params', array());
        $parameters = array();
        foreach ($this->router()->getRoute($name)->getRequirements() as $k => $v) {
            if (isset($attributes[$k])) {
                $parameters[$k] = $attributes[$k];
            }
        }
        return $this->generateUrl($name, $parameters, $referenceType);
    }

    public function generateCurrentUrl(array $parameters = array(), $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        $request = $this->request();
        $parameters = array_merge(
            $request->attributes->get('_route_params', array()),
            $request->query->all(),
            $parameters
        );
        return $this->generateUrl($request->getRouteName(), $parameters, $referenceType);
    }

    /**
     * @param $id
     * @return object
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return Handler
     */
    public function handler()
    {
        return $this->container->get('handler');
    }

    /**
     * @param $tag
     * @param array $attributes
     * @param bool $empty
     * @throws \RuntimeException
     * @return Html
     */
    public function html($tag, array $attributes = array(), $empty = false)
    {;
        $html = $this->handler()->triggerForValue(array(
                "kernel.html#{$tag}",
                'kernel.html'
            ), $attributes, $tag, $empty);

        if (!$html) {
            throw new \RuntimeException(sprintf('Html[%s] has not been defined.', $html));
        } else if (!($html instanceof Html)) {
            throw new \RuntimeException(sprintf('Html[%s] must be instance of Youngx\MVC\Html, %s given.', $tag, is_object($html) ? get_class($html) : gettype($html)));
        }

        return $html;
    }

    /**
     * @return Identity
     */
    public function identity()
    {
        static $user;
        if (null === $user) {
            $user = $this->container->get('user.identity.storage')->read();
        }
        return $user;
    }

    /**
     * @param $type
     * @param array $attributes
     * @return InputInterface
     * @throws \RuntimeException
     */
    public function input($type, array $attributes = array())
    {
        $type = str_replace('_', '-', $type);

        $input = $this->handler()->triggerForValue("kernel.input#{$type}", $attributes, $type);

        if (!$input) {
            throw new \RuntimeException(sprintf('Input[%s] has not been defined.', $type));
        } else if (!($input instanceof Html)) {
            throw new \RuntimeException(sprintf('Input[%s] must be instance of Youngx\MVC\Html, %s given.', $type, is_object($input) ? get_class($input) : gettype($input)));
        }

        $this->handler()->trigger("kernel.input.format#{$type}", $input, $type);

        return $input;
    }

    public function locate($uri)
    {
        return $this->app->locate($uri);
    }

    public function locateUrl($uri)
    {
        return $this->app->locateUrl($uri);
    }

    public function locateImageUrl($uri, $width = 0, $height = 0)
    {
        return $this->value('image-url', $uri, $width, $height);
    }

    /**
     * @param string | null $name
     * @return \Monolog\Logger
     */
    public function log($name = null)
    {
        return $this->container->get('monolog', $name ?: 'kernel');
    }

    public function login(Identity $identity, $duration = 0)
    {
        $this->container->get('user.identity.storage')->write($identity, $duration);
        $this->handler()->trigger('user.login', $identity, $duration);
    }

    public function logout()
    {
        $this->container->get('user.identity.storage')->clear();
        $this->handler()->trigger('user.logout', $this->identity());
    }

    public function matchRoute($path)
    {
        try {
            $attributes = $this->router()->match($path);
            return $attributes['_route'];
        } catch (ResourceNotFoundException $e) {
            return false;
        } catch (MethodNotAllowedException $e) {
            return false;
        }
    }

    public function permit($permission)
    {
        return (Boolean) $this->handler()->trigger(array(
                "kernel.permit#{$permission}",
                "kernel.permit"
            ), $this->identity()->getRoles(), $permission);
    }

    /**
     * @param string $url
     * @param int $status
     * @param array $headers
     * @return Response|static
     */
    public function redirectResponse($url = '', $status = 302, $headers = array())
    {
        $url = $this->request()->get('returnUrl') ?: $url;
        if (!$url) {
            $url = $this->request()->getBaseUrl();
        }
        return RedirectResponse::create($url, $status, $headers);
    }

    public function render($path, array $variables = array())
    {
        return $this->templating()->render($path, $variables);
    }

    /**
     * @param $path
     * @param array $variables
     * @param int $statusCode
     * @return Response
     */
    public function renderResponse($path, array $variables = array(), $statusCode = 200)
    {
        return $this->response($this->render($path, $variables), $statusCode);
    }

    /**
     * @return RenderableResponse
     */
    public function renderableResponse()
    {
        return new RenderableResponse($this);
    }

    /**
     * @param $content
     * @param int $statusCode
     * @return Response
     */
    public function response($content = '', $statusCode = 200)
    {
        return new Response($content, $statusCode);
    }

    /**
     * @return \Youngx\Database\Repository
     */
    public function repository()
    {
        return $this->container->get('repository');
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->container->get('request');
    }

    /**
     * @return Router
     */
    public function router()
    {
        return $this->container->get('router');
    }

    /**
     * @return \Youngx\Database\Schema
     */
    public function schema()
    {
        return $this->container->get('schema');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function session()
    {
        return $this->container->get('session');
    }

    /**
     * @return \Youngx\MVC\Templating\EngineInterface
     */
    public function templating()
    {
        return $this->container->get('templating');
    }

    /**
     * @param $key
     * @return mixed
     */
    public function value($key)
    {
        $arguments = func_get_args();
        $arguments[0] = "kernel.value#{$key}";
        return call_user_func_array(array($this->handler(), 'triggerForValue'), $arguments);
    }

    /**
     * @param $name
     * @param array $config
     * @return Widget
     * @throws \RuntimeException
     */
    public function widget($name, array $config = array())
    {
        $class = '\\' . (strpos($name, '@') === false ? "Youngx\\MVC\\Widget\\{$name}Widget" : $this->app->resolveClass("Widget:{$name}"));

        return new $class($this, $config);
    }
}