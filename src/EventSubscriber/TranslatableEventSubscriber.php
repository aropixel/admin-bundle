<?php

namespace Aropixel\AdminBundle\EventSubscriber;

use Aropixel\AdminBundle\Entity\TranslatableInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Gedmo\Translatable\Translatable;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslatableEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     * @return void
     */
    public function postLoad(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     * @return void
     */
    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::postLoad => Events::postLoad, Events::prePersist => Events::prePersist];
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $lifecycleEventArgs
     * @return void
     */
    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        /** @var TranslatableInterface $entity */
        $entity = $lifecycleEventArgs->getObject();
        if (!$entity instanceof Translatable) {
            return;
        }

        $fallbackLocale = $this->provideFallbackLocale();
        if ($fallbackLocale) {
            $entity->setTranslatableLocale($fallbackLocale);
        }

        if ($currentLocale = $this->provideCurrentLocale()) {
            $entity->setTranslatableLocale($currentLocale);
        }
    }

    private function provideCurrentLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest instanceof Request) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ('' !== $currentLocale) {
            return $currentLocale;
        }

        return null;
    }

    public function provideFallbackLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null !== $currentRequest) {
            return $currentRequest->getDefaultLocale();
        }

        try {

            return (string) $this->parameterBag->get('kernel.default_locale');
        } catch (ParameterNotFoundException|\InvalidArgumentException) {
            return null;
        }
    }
}
