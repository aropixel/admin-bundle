<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Aropixel\AdminBundle\Domain\Menu\Model\Link;
use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AdminMenuBuilder implements AdminMenuBuilderInterface
{
    public const MENU_ID = 'admin';

    public function __construct(
        protected ParameterBagInterface $parameterBag
    ) {
    }

    public function buildMenu(): Menu
    {
        $menu = new Menu(self::MENU_ID);
        $menu->addItem(new Link('Administrateurs', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-user-cog']));
        $menu->addItem(new Link('Nouvel administrateur', 'aropixel_admin_user_new', [], ['icon' => 'fas fa-user-cog']));

        return $menu;
    }

}
