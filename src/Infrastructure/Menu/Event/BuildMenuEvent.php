<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Event;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Symfony\Contracts\EventDispatcher\Event;

class BuildMenuEvent extends Event
{
    public const NAME = 'aropixel.admin.build.menu';

    public function __construct(
        private readonly Menu $menu
    ) {
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }
}
