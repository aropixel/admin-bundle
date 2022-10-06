<?php

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Security\Exception\TooOldPasswordException;
use Aropixel\AdminBundle\Security\Passport\Badge\TooOldPasswordBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class TooOldPasswordEventListener implements EventSubscriberInterface
{

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(TooOldPasswordBadge::class)) {
            return;
        }

        /** @var TooOldPasswordBadge $badge */
        $badge = $passport->getBadge(TooOldPasswordBadge::class);
        $user = $badge->getUser();

        if (!$badge->isResolved()) {
            throw new TooOldPasswordException($user);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'checkPassport'];
    }

}
