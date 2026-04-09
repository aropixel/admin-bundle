<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Event\BuildMenuEvent;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MenuBuilder implements MenuBuilderInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function buildMenu(string $id = 'menu'): Menu
    {
        // Create the menu
        $menu = new Menu($id);

        // Send an event, so event listeners, in app or in any bundle, can add items to the menu
        $buildMenuEvent = new BuildMenuEvent($menu);
        $this->eventDispatcher->dispatch($buildMenuEvent, BuildMenuEvent::NAME);

        return $menu;
    }
}
