<?php

namespace Youngx\MVC;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Youngx\EventHandler\Handler;
use Youngx\MVC\Event\FilterResponseEvent;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\MVC\Event\GetResponseForExceptionEvent;
use Youngx\MVC\Exception\HttpException;

class Dispatcher implements DispatcherInterface
{
    private $handler;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(Handler $handler, LoggerInterface $logger)
    {
        $this->handler = $handler;
        $this->logger = $logger;
    }

    public function dispatch(Request $request)
    {
        try {
            return $this->doDispatch($request);
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }
    
    protected function doDispatch(Request $request)
    {
        $this->logger->debug('dispatch: request', array(
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
                'ip' => $request->getClientIp(),
            ));

        $event = new GetResponseEvent();
        $this->handler->trigger('kernel.request', $event, $request);
        
        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request);
        }

        $this->handler->trigger('kernel.routing', $event, $request);

        $this->logger->debug('dispatch: routing', array(
                'route' => $request->getRouteName(),
                'controller' => $request->attributes->get('_controller'),
                'attributes' => $request->attributes->get('_route_params', array())
            ));

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request);
        }

        $this->handler->trigger('kernel.controller', $event, $request);
        if (!$event->hasResponse()) {
            throw new \LogicException('Controller does not return a response.');
        }

        return $this->filterResponse($event->getResponse(), $request);
    }
    
    protected function handleException(\Exception $e, Request $request)
    {
        $event = new GetResponseForExceptionEvent($e);
        $this->handler->trigger('kernel.exception', $event, $request);
        
        // a listener might have replaced the exception
        $e = $event->getException();
        
        if (!$event->hasResponse()) {
            throw $e;
        }
        
        $response = $event->getResponse();
        
        // the developer asked for a specific status code
        if ($response->headers->has('X-Status-Code')) {
            $response->setStatusCode($response->headers->get('X-Status-Code'));
        
            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof HttpException) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }
        
        try {
            return $this->filterResponse($response, $request);
        } catch (\Exception $e) {
            return $response;
        }
    }
    
    protected function filterResponse(Response $response, Request $request)
    {
        $event = new FilterResponseEvent($response);
        $this->handler->trigger('kernel.response', $event, $request);

        $this->logger->debug('dispatch: response', array(
                'status code' => $event->getResponse()->getStatusCode(),
                'charset' => $event->getResponse()->getCharset()
            ));

        return $event->getResponse();
    }
}
