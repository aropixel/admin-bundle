<?php

namespace Aropixel\AdminBundle\Component\Menu;

use Aropixel\AdminBundle\Component\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Component\Menu\Model\ItemInterface;
use Aropixel\AdminBundle\Component\Menu\Model\SubMenu;

class LinkMatcher
{
    public function __construct(
        private readonly AdminMenuBuilderInterface $adminMenuBuilder
    ) {
    }

    public function getLink(string $id): ?ItemInterface
    {
        $menus = $this->adminMenuBuilder->buildMenu();

        foreach ($menus as $menu) {
            foreach ($menu->getItems() as $item) {
                if ($item->getId() == $id) {
                    return $item;
                }

                if ($item instanceof SubMenu) {
                    if ($defaultChild = $item->getDefaultChild()) {
                        if ($defaultChild->getId() == $id) {
                            return $defaultChild;
                        }
                    }
                }
            }
        }

        return null;
    }
}
