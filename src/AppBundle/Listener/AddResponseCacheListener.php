<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class AddResponseCacheListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $headers = $response->headers;

        $headers->addCacheControlDirective('no-cache', true);
        $headers->addCacheControlDirective('max-age', 0);
        $headers->addCacheControlDirective('must-revalidate', true);
        $headers->addCacheControlDirective('no-store', true);
    }
}