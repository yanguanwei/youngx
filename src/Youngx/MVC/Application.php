<?php

namespace Youngx\MVC;

use Youngx\DI\Container;
use Youngx\DI\DefinitionCollection;
use Youngx\DI\DefinitionProcessor;
use Youngx\DI\Dumper;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Debug;
use Youngx\EventHandler\Registration;

abstract class Application
{
    /**
     * @var string
     */
    protected $environment;
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var Bundle[]
     */
    protected $bundles = array();
    /**
     * 
     * @var Locator
     */
    protected $locator;
    /**
     * 
     * @var Container
     */
    protected $container;

    protected $configuration;

    protected $resolving = array();

    protected $startTime;
    protected $endTime;
    
    public function __construct($environment, $debug = false)
    {
        $this->startTime = microtime(true);
        $this->environment = $environment;
        $this->debug = $debug;
        $this->initialize();
    }

    /**
     * @return Bundle[]
     */
    abstract protected function registerBundles();
    /**
     * @return array
     */
    abstract protected function registerConfiguration();

    abstract protected function registerLocations(Locator $locator);
    
    protected function initialize()
    {
        Debug::enable();

        $this->bundles = array();
        foreach ($this->registerBundles() as $bundle) {
            $this->bundles[$bundle->getName()] = $bundle;
        }

        $this->registerLocations($this->locator = new Locator());

        if (!$this->hasBuilt()) {
            $this->build();
        }

        $container = $this->getContainer();
        $container->register('app', $this, __CLASS__);
        $container->register('context', $context = new Context($this, $container), __NAMESPACE__ . '\Context');

        $handler = $context->handler();
        foreach ($this->getBundles() as $id => $bundle) {
            $container->register("bundle.{$id}", $bundle);
            if ($bundle instanceof Registration) {
                $handler->addRegistration($bundle);
            }
        }

        foreach ($this->getBundles() as $bundle) {
            $bundle->initialize($context);
        }
    }

    public function hasBuilt()
    {
        return !$this->isDebug() && is_file($this->getContainerPath());
    }

    public function build()
    {
        $builders = array();

        foreach ($this->getBundles() as $bundle) {
            $bundleBuilder = get_class($bundle) . "Builder";
            if (class_exists($bundleBuilder)) {
                $bundleBuilder = '\\' . $bundleBuilder;
                $builders[] = $bundleBuilder = new $bundleBuilder($this);
            }

            foreach ($bundle->modules() as $module) {
                $moduleBuilder = $bundle->getNamespace() . '\Module\\' . "{$module}Module" . '\\' . "{$module}ModuleBuilder";
                if (class_exists($moduleBuilder)) {
                    $moduleBuilder = '\\' . $moduleBuilder;
                    $builders[] = new $moduleBuilder($this);
                }
            }
        }

        $this->building($builders);
    }

    /**
     * @param \Youngx\DI\ContainerBuilder[] $builders
     */
    protected function building(array $builders)
    {
        $collection = new DefinitionCollection();
        foreach($builders as $builder) {
            $builder->collect($collection);
        }

        foreach ($collection->getDefinitions() as $definition) {
            $definition->setClass($this->resolveClass($definition->getClass()));
            $typeClasses = array();
            foreach ($definition->getTypeClasses() as $class) {
                $typeClasses[] = $this->resolveClass($class);
            }
            $definition->addTypeClass($typeClasses);
        }

        $processor = $processor = new DefinitionProcessor($collection->getDefinitions());
        foreach($builders as $builder) {
            $builder->process($processor);
        }

        $dump = new Dumper(
            $this->getContainerClass(),
            'Youngx\DI\Container',
            $this->getConfiguration('parameters', array()),
            $processor->getDefinitions(),
            $processor->getTags()
        );

        file_put_contents($this->getContainerPath(), "<?php\n{$dump->dump()}\n");

        $container = $this->getContainer(true);
        foreach ($builders as $builder) {
            $builder->compiled($container, $processor);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function dispatch(Request $request)
    {
        $this->getContainer()->register('request', $request);
        return $this->getContainer()->get('dispatcher')->dispatch($request);
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function generateClass($bundle, $name, $type, $module = null, $suffix = true)
    {
        $name = strtr($name, array('.' => '\\'));
        return $this->getBundle($bundle)->getNamespace() . ($module ? "\\Module\\{$module}Module" : ''). "\\{$type}\\{$name}" . ($suffix ? $type : '');
    }

    /**
     * @param $name
     * @return Bundle
     * @throws \InvalidArgumentException
     */
    public function getBundle($name)
    {
        if (!isset($this->bundles[$name])) {
            throw new \InvalidArgumentException(sprintf('Bundle[%s] has not been registered.', $name));
        }
        return $this->bundles[$name];
    }

    /**
     * @return Bundle[]
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @param string|null $key
     * @param null | mixed $default
     * @return mixed
     */
    public function getConfiguration($key = null, $default = null)
    {
        if (null === $this->configuration) {
            $this->configuration = $this->registerConfiguration();
        }

        return null === $key ? $this->configuration
            : (isset($this->configuration[$key]) ? $this->configuration[$key] : $default);
    }

    /**
     * @param bool $reConstruct
     * @return Container
     */
    public function getContainer($reConstruct = false)
    {
        if (null === $this->container || $reConstruct) {
            require_once $this->getContainerPath();
            $class = '\\' . $this->getContainerClass();
            $this->container = new $class();
        }
        return $this->container;
    }

    public function getContainerClass()
    {
        $environment = ucfirst($this->environment);
        return "Youngx{$environment}Container";
    }

    public function getContainerPath()
    {
        return $this->locator->locate("cache://{$this->getContainerClass()}.php");
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return Locator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    public function locate($uri)
    {
        return $this->getLocator()->locate($uri);
    }

    public function locateUrl($uri)
    {
        return $this->getLocator()->locateUrl($uri);
    }

    /**
     * @param $alias
     * @param bool $suffix
     * @return string
     */
    public function resolveClass($alias, $suffix = true)
    {
        if (false !== strpos($alias, '@') && preg_match('/^(([a-zA-Z0-9]+):)?([a-zA-Z0-9\.]+)@([a-zA-Z0-9]+)(:([a-zA-Z0-9]+))?$/', $alias, $match)) {
            $type = $match[2];
            $name = $match[3];
            $bundle = $match[4];
            $module = isset($match[6]) ? $match[6] : null;
            return $this->generateClass($bundle, $name, $type, $module, $suffix);
        }
        return $alias;
    }
}
