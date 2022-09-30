<?php

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Security\Exception\TooOldPasswordAuthenticationException;
use Aropixel\AdminBundle\Security\Passport\Badge\UpdateOldPasswordBadge;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class TooOldPasswordEventListener implements EventSubscriberInterface
{

    public function checkPassport(CheckPassportEvent $event): void
    {
        dd('gdh');
        $passport = $event->getPassport();
        if (!$passport->hasBadge(UpdateOldPasswordBadge::class)) {
            return;
        }

        /** @var UpdateOldPasswordBadge $badge */
        $badge = $passport->getBadge(UpdateOldPasswordBadge::class);
        $user = $badge->getUser();

        if (!$badge->isResolved()) {
            throw new TooOldPasswordAuthenticationException($user);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [CheckPassportEvent::class => 'checkPassport'];
    }

}
