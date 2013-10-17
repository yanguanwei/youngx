<?php

namespace Youngx\Kernel;

use Youngx\Kernel\Container\DefinitionCollection;
use Youngx\Kernel\Container\DefinitionProcessor;
use Youngx\Kernel\Container\Dumper;
use Youngx\Kernel\Service\Definition;
use Youngx\Kernel\Handler\ListenerRegistration;

class Builder
{
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var \Youngx\Kernel\Container\ContainerBuilderInterface[]
     */
    protected $builders;
    /**
     * @var \Youngx\Kernel\Container\DefinitionProcessor
     */
    protected $processor;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @return boolean
     */
    public function isBuilt()
    {
        return !$this->application->isDebug() && is_file($this->application->getContainerPath());
    }

    /**
     * @return \Youngx\Kernel\Container\ContainerBuilderInterface[]
     */
    protected function getBuilders()
    {
        if ($this->builders === null) {
            $builders = array();
            foreach ($this->application->getBundles() as $bundle) {
                $bundleBuilder = get_class($bundle) . "Builder";
                if (class_exists($bundleBuilder)) {
                    $bundleBuilder = '\\' . $bundleBuilder;
                    $builders[] = $bundleBuilder = new $bundleBuilder($this->application);
                    foreach ($bundle->modules() as $module) {
                        $moduleBuilder = $bundle->getNamespace() . '\Module\\' . "{$module}Module" . '\\' . "{$module}ModuleBuilder";
                        if (class_exists($moduleBuilder)) {
                            $moduleBuilder = '\\' . $moduleBuilder;
                            $builders[] = new $moduleBuilder($this->application);
                        }
                    }
                }
            }
            $this->builders = $builders;
        }
        return $this->builders;
    }

    public function build()
    {
        $collection = new DefinitionCollection();
        $collection->setParameters($this->application->getConfiguration('parameters', array()));

        $builders = $this->getBuilders();
        foreach($builders as $builder) {
            $builder->collect($collection);
        }

        $this->processor = $processor = new DefinitionProcessor($collection->getParameters(), $collection->getDefinitions());
        foreach($builders as $builder) {
            $builder->process($processor);
        }

        $this->processListeners($processor);

        $dump = new Dumper($this->application->getContainerClass(), 'Youngx\Kernel\Container', $processor);
        file_put_contents($this->application->getContainerPath(), "<?php\n{$dump->dump()}\n");
    }

    protected function processListeners(DefinitionProcessor $processor)
    {
        $handler = $processor->getDefinition('handler');
        foreach ($processor->getTaggedDefinitions('listener') as $id => $definition) {
            $handler->call('addServiceRegistration', array($id, $definition->getClass()));
        }
    }

    public function complete()
    {
        foreach ($this->getBuilders() as $builder) {
            $builder->complete($this->processor);
        }
    }
}