<?php

namespace Youngx\MVC\Action;

use Youngx\MVC\Action;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\Database\Entity;
use Youngx\MVC\RenderableResponse;
use Youngx\MVC\Widget\FormWidget;

abstract class DeleteAction extends ConfirmAction
{
    /**
     * @var Entity[]
     */
    private $entities = array();

    /**
     * @return string
     */
    abstract protected function entityType();

    protected function initRequest()
    {
        if ($this->context->request()->isMethod('POST')) {
            $this->entities = $this->loadEntitiesFromPostRequest();
        } else {
            $entity = $this->loadEntityFromGetRequest();
            $this->entities = $entity ? array($entity) : array();
        }
    }

    protected function formatFormWidget(FormWidget $formWidget)
    {
        foreach ($this->entities as $entity) {
            $formWidget->add('entity-' . $entity->identifier(),
                $this->context->input('hidden', array(
                        '#value' => $entity->identifier(),
                        'name' => $entity->primaryKey().'[]'
                    ))
            );
        }

        if (!$this->entities) {
            $formWidget->getSubmitHtml()->set('disabled', true);
        }
    }

    protected function getMessage()
    {
        if ($this->entities) {
            return $this->getMessageForEntities($this->entities);
        } else {
            return $this->getMessageForEmpty();
        }
    }

    protected function getMessageForEmpty()
    {
        return '没有任何可删除的记录';
    }

    /**
     * @param Entity[] $entities
     * @return string
     */
    protected function getMessageForEntities(array $entities)
    {
        return '您确认要删除吗？';
    }

    protected function loadEntityFromGetRequest()
    {
        $entityType = $this->entityType();
        $primaryKey = $this->context->schema()->getPrimaryKey($entityType);
        $id = $this->context->request()->query->get($primaryKey);

        return $id ? $this->context->repository()->load($entityType, $id) : null;
    }

    /**
     * @return Entity[]
     */
    protected function loadEntitiesFromPostRequest()
    {
        $entityType = $this->entityType();
        $primaryKey = $this->context->schema()->getPrimaryKey($entityType);
        $ids = (array) $this->context->request()->request->get($primaryKey);

        return $ids ? $this->context->repository()->loadMultiple($entityType, $ids) : array();
    }

    protected function validate()
    {
        return $this->validateForEntities($this->entities);
    }

    /**
     * @param array $entities
     * @return string
     */
    protected function validateForEntities(array $entities)
    {
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Exception
     * @return mixed
     */
    protected function submit(GetResponseEvent $event)
    {
        try {
            $this->context->db()->beginTransaction();
            $this->delete($this->entities, $event);
            $this->context->db()->commit();
            $this->context->flash()->add('success', $this->getSuccessMessage($this->entities));
        } catch (\Exception $e) {
            $this->context->db()->rollBack();
            throw $e;
        }
    }

    /**
     * @param Entity[] $entities
     * @return string
     */
    protected function getSuccessMessage(array $entities)
    {
        return sprintf('成功删除<em>%s</em>条记录', count($entities));
    }

    /**
     * @param Entity[] $entities
     * @param GetResponseEvent $event
     */
    protected function delete(array $entities, GetResponseEvent $event)
    {
        foreach ($entities as $entity) {
            $entity->delete();
        }
    }
}