<?php

namespace Youngx\MVC\Event;

class GetResponseForExceptionEvent extends GetResponseEvent
{
    protected $exception;
    
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }
    
    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    public function setException(\Exception $e)
    {
        $this->exception = $e;
    }
}
