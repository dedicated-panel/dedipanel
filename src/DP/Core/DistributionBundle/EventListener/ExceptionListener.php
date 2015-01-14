<?php

namespace DP\Core\DistributionBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

class ExceptionListener
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (strpos($this->request->getPathInfo(), '/installer/') === false) return;

        $exception = $event->getException();

        if ($exception instanceof InsufficientAuthenticationException
        || $exception instanceof AccessDeniedException) {
            $event->setResponse(new Response($exception->getMessage(), 403));
        }
    }
}
