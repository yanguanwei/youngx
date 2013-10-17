<?php

namespace Youngx\MVC\Action;

use Symfony\Component\HttpFoundation\Response;
use Youngx\MVC\Action;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\MVC\Form;
use Youngx\Util\SortableArray;

abstract class WizardAction extends Action
{
    /**
     * @var WizardContext
     */
    protected $wizardContext;

    abstract  protected function collectActions(WizardActionCollection $collection);

    abstract protected function finish(GetResponseEvent $event, $action);

    public function id()
    {
        return 'wizard';
    }

    public function runPostRequest()
    {
        $this->initRequest();
        $action = $this->context->action($this->wizardContext->getActiveAction(), $this->wizardContext->getActiveActionData());
        $this->initAction($action);
        if ($action instanceof Form) {
            $response = $action->runPostRequest();
            if ($action->hasError()) {
                return $this->renderResponse($response);
            } else {
                $this->wizardContext->nextStep();
                return $this->nextResponse($action);
            }
        } else {
            $this->wizardContext->nextStep();
            return $this->nextResponse($action);
        }
    }

    public function runGetRequest()
    {
        $step = $this->context->request()->query->get('step');
        if (null === $step) {
            $this->context->session()->remove('wizard_context');
        }
        $this->initRequest($step);
        return $this->nextResponse();
    }

    protected function initRequest($step = null)
    {
        $this->wizardContext = $this->initWizardContext();
        if (null !== $step) {
            $this->wizardContext->setStep($step);
        }
    }

    protected function initWizardContext()
    {
        $wizardContext = $this->context->session()->get('wizard_context');
        if (!$wizardContext) {
            $this->collectActions($collection = new WizardActionCollection());
            $actions = $stepTitles = $steps = $actionsData = array();
            foreach ($collection->all() as $name => $info) {
                list($title, $actionClass, $data) = $info;
                $stepTitles[$name] = $title;
                $actions[$name] = $actionClass;
                $actionsData[$name] = $data;
                 $steps[] = $name;
            }
            if (empty($steps)) {
                throw new \Exception(sprintf('There is no steps to run.'));
            }
            $wizardContext = new WizardContext($steps, $stepTitles, $actions, $actionsData);
        }
        return $wizardContext;
    }

    /**
     * @param Action $prevAction
     * @return Response
     */
    protected function nextResponse(Action $prevAction = null)
    {
        if ($this->wizardContext->isFinished()) {
            $this->context->session()->remove('wizard_context');
            $event = new GetResponseEvent();
            $this->finish($event, $prevAction);
            return $event->getResponse();
        } else {
            $action = $this->context->action($this->wizardContext->getActiveAction(), $this->wizardContext->getActiveActionData());
            $this->initAction($action, $prevAction);
            $this->context->session()->set('wizard_context', $this->wizardContext);
            if ($prevAction) {
                return $this->context->redirectResponse($this->context->generateCurrentUrl(array(
                            'step' => $this->wizardContext->getStep()
                        )));
            } else {
                return $this->renderResponse($action->runGetRequest());
            }
        }
    }

    protected function initAction($action, $prevAction = null)
    {
        if (method_exists($this, $method = 'init'.ucfirst($this->wizardContext->getActiveStepName()).'Action')) {
            $this->$method($action, $prevAction);
        }
    }

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->wizardContext->getStep();
    }

    /**
     * @return array
     */
    public function getStepTitles()
    {
        return $this->wizardContext->getStepTitles();
    }
}

class WizardContext
{
    private $variables = array();
    private $steps;
    private $stepTitles;
    private $actions;
    private $step = 0;
    private $actionsData;

    public function __construct(array $steps, array $stepTitles, array $actions, array $actionsData)
    {
        $this->steps = $steps;
        $this->stepTitles = $stepTitles;
        $this->actions = $actions;
        $this->actionsData = $actionsData;
    }

    public function setStep($step)
    {
        if ($step > count($this->steps) - 1) {
            throw new \Exception();
        }

        $this->step = $step;

        return $this;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getActiveStepName()
    {
        return $this->steps[$this->step];
    }

    public function getActiveStepTitle()
    {
        return $this->stepTitles[$this->getActiveStepName()];
    }

    public function getStepTitles()
    {
        return $this->stepTitles;
    }

    public function nextStep()
    {
        $this->step++;

        return $this;
    }

    public function isFinished()
    {
        return $this->step === count($this->steps);
    }

    /**
     * @return string
     */
    public function getActiveAction()
    {
        return $this->actions[$this->getActiveStepName()];
    }

    /**
     * @return array
     */
    public function getActiveActionData()
    {
        return $this->actionsData[$this->getActiveStepName()];
    }

    public function add($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->variables);
    }

    public function get($key, $default = null)
    {
        return isset($this->variables[$key]) ? $this->variables[$key] : $default;
    }
}

class WizardActionCollection
{
    /**
     * @var \Youngx\Util\SortableArray
     */
    private $actions;

    public function __construct()
    {
        $this->actions = new SortableArray();
    }

    public function add($name, $title, $actionClass, array $data = array(), $sort = 0)
    {
        $this->actions->set($name, array($title, $actionClass, $data), $sort);

        return $this;
    }

    public function sort($name, $sort)
    {
        $this->actions->sort($name, $sort);

        return $this;
    }

    /**
     * @return Action[]
     */
    public function all()
    {
        return $this->actions->all();
    }
}