<?php

namespace Youngx\Database;

class Schema
{
    protected $entityClasses = array();

    protected $relationships = array();

    protected $typeFlips = array(
        'one_many' => 'many_one',
        'many_one' => 'one_many',
        'one_one' => 'one_one',
        'many_many' => 'many_many'
    );

    public function __construct(array $entityClasses, array $typeRelationships = array())
    {
        $this->entityClasses = $entityClasses;

        foreach ($typeRelationships as $type => $relationships) {
            foreach ($relationships as $key => $relationship) {
                $this->relationships[$type][$key] = $relationship;
                if ($relationship['reverse']) {
                    $this->relationships[$relationship['entityType']][$relationship['reverse']] = array(
                        'entityType' => $type,
                        'condition' => array_flip($relationship['condition']),
                        'relation' => $this->typeFlips[$relationship['relation']],
                        'reverse' => $key
                    );
                }
            }
        }
    }

    /**
     * @param $entityType
     * @return string
     * @throws \RuntimeException
     */
    public function getEntityClass($entityType)
    {
        if (!isset($this->entityClasses[$entityType])) {
            throw new \RuntimeException(sprintf('Entity[%s] has not been registered.', $entityType));
        }
        return $this->entityClasses[$entityType];
    }

    public function getTable($entityType)
    {
        $class = $this->getEntityClass($entityType);
        return $class::table();
    }

    public function getPrimaryKey($entityType)
    {
        $class = $this->getEntityClass($entityType);
        return $class::primaryKey();
    }

    public function getFields($entityType)
    {
        $class = $this->getEntityClass($entityType);
        return $class::fields();
    }

    public function getRelationship($entityType, $field)
    {
        $relationships = $this->getRelationships();
        if (isset($relationships[$entityType][$field])) {
            return $relationships[$entityType][$field];
        }
    }

    public function hasEntityType($entityType)
    {
        return isset($this->entityClasses[$entityType]);
    }

    public function hasRelationship($entityType, $field)
    {
        $relationships = $this->getRelationships();
        return isset($relationships[$entityType][$field]);
    }

    public function getRelationships()
    {
        return $this->relationships;
    }
}