<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu;

use Aropixel\AdminBundle\Domain\Menu\Model\ItemInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Domain\Menu\Model\SubMenu;

class LinkMatcher
{
    public function __construct(
        private readonly AdminMenuBuilderInterface $adminMenuBuilder
    ) {
    }

    public function getLink(string $id): ?ItemInterface
    {
        foreach ($this->adminMenuBuilder->buildMenu()->getItems() as $item) {

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

        return null;
    }
}
