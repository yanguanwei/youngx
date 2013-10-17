<?php

namespace Youngx\MVC;

use Symfony\Component\HttpFoundation\Response;

interface DispatcherInterface
{
    /**
     * @param Request $request
     * @return Response
     */
    public function dispatch(Request $request);
}
