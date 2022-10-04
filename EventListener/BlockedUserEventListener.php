<?php

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Security\Passport\Badge\BlockedUserBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Aropixel\AdminBundle\Security\Exception\BlockedUserException;

class BlockedUserEventListener implements EventSubscriberInterface
{

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(BlockedUserBadge::class)) {
            return;
        }

        /** @var BlockedUserBadge $badge */
        $badge = $passport->getBadge(BlockedUserBadge::class);
        $user = $badge->getUser();

        if (!$badge->isResolved()) {
            throw new BlockedUserException($user);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'checkPassport'];
    }

}
