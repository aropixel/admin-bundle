<?php


namespace Aropixel\AdminBundle\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;


class LocaleListener
{

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_locale')) {
            $localeParameter = $request->attributes->get('_locale');
            $request->setLocale($localeParameter);
        }

    }

}
