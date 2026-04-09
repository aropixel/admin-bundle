<?php

namespace Aropixel\AdminBundle\Component\Security\EventListener;

use Aropixel\AdminBundle\Component\Security\Exception\DisabledUserException;
use Aropixel\AdminBundle\Component\Security\Passport\Badge\DisabledUserBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class DisabledUserEventListener implements EventSubscriberInterface
{
    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(DisabledUserBadge::class)) {
            return;
        }

        /** @var DisabledUserBadge $badge */
        $badge = $passport->getBadge(DisabledUserBadge::class);

        $user = $badge->getUser();

        if (!$badge->isResolved()) {
            throw new DisabledUserException($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'checkPassport'];
    }
}
