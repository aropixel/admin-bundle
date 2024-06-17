<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\EventListener;

use Aropixel\AdminBundle\Infrastructure\Security\Exception\TooOldLastLoginException;
use Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge\TooOldLastLoginBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class TooOldLastLoginEventListener implements EventSubscriberInterface
{
    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(TooOldLastLoginBadge::class)) {
            return;
        }

        /** @var TooOldLastLoginBadge $badge */
        $badge = $passport->getBadge(TooOldLastLoginBadge::class);

        $user = $badge->getUser();

        if (!$badge->isResolved()) {
            throw new TooOldLastLoginException($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'checkPassport'];
    }
}
