<?php

namespace Youngx\MVC\Event;

use Symfony\Component\HttpFoundation\Response;

class GetResponseEvent
{
    protected $response;

    public function __construct(Response $response = null)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;    
    }
    
    public function hasResponse()
    {
        return null !== $this->response;
    }
    
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
