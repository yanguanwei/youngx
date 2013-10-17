<?php

namespace Youngx\DI;

class Dumper
{
    protected $class;
    protected $baseClass;
    protected $idMaps;
    protected $parameters;
    protected $classMaps;
    protected $definitions;
    protected $tags;

    /**
     * @param $class
     * @param $baseClass
     * @param array $parameters
     * @param Definition[] $definitions
     * @param array $tags
     */
    public function __construct($class, $baseClass, array $parameters, array $definitions, array $tags)
    {
        $this->class = $class;
        $this->baseClass = $baseClass;
        $this->parameters = $parameters;
        $this->definitions = $definitions;
        $this->tags = $tags;
    }

    public function dump()
    {
        $taggedIdClasses = array();
        foreach ($this->tags as $tag => $ids) {
            foreach ($ids as $id) {
                $taggedIdClasses[$tag][$id] = $this->definitions[$id]->getClass();
            }
        }

        $typeClasses = array();
        foreach ($this->definitions as $id => $definition) {
            foreach ($definition->getTypeClasses() as $class) {
                $typeClasses[$class] = $id;
            }
        }

        $subscribers = $subjects = array();
        foreach ($this->definitions as $id => $definition) {
            foreach ($definition->getSubjects() as $subject => $method) {
                $subjects[$subject][$id] = $method;
                $subscribers[$id][$subject] = $method;
            }
        }

        $methods = array();
        foreach($this->definitions as $id => $definition) {
            $methods[] = $this->parseDefinition($id, $definition, $typeClasses);
        }
        array_unshift($methods, $this->parseConstructor($taggedIdClasses, $typeClasses, $subjects, $subscribers));
        $methods = implode("\n", $methods);

        $codes = $this->beginClass($this->class, $this->baseClass);
        $codes .= $methods;
        $codes .= $this->endClass();

        return $codes;
    }

    protected function beginClass($class, $extendedClass = null)
    {
        return "class {$class}" . ($extendedClass ? " extends {$extendedClass}" : '') . "\n{\n";
    }

    protected function parseConstructor(array $taggedIdClasses, array $typeClasses, array $subjects, array $subscribers)
    {
        $containerTypeClasses[] = 'Youngx\DI\Container';
        $containerTypeClasses[] = $this->baseClass;
        $containerTypeClasses = array_unique($containerTypeClasses);

        $codes = "    public function __construct()\n    {\n";
        $codes .= "        \$this->typeClasses = " . var_export($typeClasses, true) . ";\n";
        $codes .= "        \$this->subjects = " . var_export($subjects, true) . ";\n";
        $codes .= "        \$this->subscribers = " . var_export($subscribers, true) . ";\n";
        $codes .= "        \$this->taggedIdClasses = " . var_export($taggedIdClasses, true) . ";\n";
        $codes .= "        \$this->parameters = " . var_export($this->parameters, true) . ";\n";
        $codes .= "        \$this->register('container', \$this, ".var_export($containerTypeClasses, true).");\n";
        $codes .= "        parent::__construct();\n";
        $codes .= "    }\n";
        return $codes;
    }

    protected function parseDefinition($id, Definition $definition, array $typeClasses)
    {
        $method = 'get' . implode(array_map('ucfirst', explode('.', strtr($id, array('_' => '.', '-' => '.')))));
        $class = '\\' . $definition->getClass();

        $requireInputArguments = array();
        foreach ($definition->getRequireInputArguments() as $name => $default) {
            $requireInputArguments[] = "\${$name}" . ($default === null ? '' : ( ' = ' . $this->parseValue($default)));
        }
        $requireInputArguments = implode(', ', $requireInputArguments);

        $codes = "    protected function {$method}({$requireInputArguments})\n    {\n";
        $codes .= "        \$_service = new {$class}({$this->parseArguments($definition->getResolvedArguments($typeClasses))});\n";

        foreach ($definition->getProperties() as $property => $value) {
            $codes .= "        \$_service->{$property} = {$this->parseValue($value)};\n";
        }

        foreach ($definition->getMethods() as $method) {
            $codes .= "        \$_service->{$method->getMethod()}({$this->parseArguments($method->getArguments())});\n";
        }

        $codes .= "        return \$_service;\n";
        $codes .= "    }\n";

        return $codes;
    }

    protected function parseArguments(array $arguments)
    {
        $parsed = array();
        foreach ($arguments as $argument) {
            $parsed[] = $this->parseValue($argument);
        }
        return implode(', ', $parsed);
    }

    protected function parseValue($value)
    {
        if (is_string($value)) {
            return "'" . strtr($value, "'", "\\'") . "'";
        } else if (is_array($value)) {
            return var_export($value, true);
        } else if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } else if (null === $value) {
            return 'null';
        } else {
            return (string) $value;
        }
    }

    protected function endClass()
    {
        return "}\n";
    }

    protected function resolveArguments(array $arguments)
    {
        $resolved = array();
        foreach ($arguments as $value) {
            $resolved[] = $this->resolveValue($value);
        }
        return implode(', ', $resolved);
    }

    protected function resolveValue($value)
    {
        if (is_string($value)) {
            return "'" . strtr($value, "'", "\\'") . "'";
        } else if (is_array($value)) {
            return var_export($value, true);
        } else if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } else if (null === $value) {
            return 'null';
        } else {
            return (string) $value;
        }
    }
}