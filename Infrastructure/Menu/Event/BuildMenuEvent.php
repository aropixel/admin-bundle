<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 12:00
 */

namespace Aropixel\AdminBundle\Infrastructure\Menu\Event;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Symfony\Contracts\EventDispatcher\Event;

class BuildMenuEvent extends Event
{
    public const NAME = 'aropixel.admin.build.menu';

    private Menu $menu;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return Menu
     */
    public function getMenu(): Menu
    {
        return $this->menu;
    }


}
