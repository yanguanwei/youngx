<?php

namespace Youngx\MVC\Event;

use Symfony\Component\HttpFoundation\Response;

class FilterResponseEvent
{
    protected $response;
    
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    
    /**
     * 
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
