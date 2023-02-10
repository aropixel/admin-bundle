<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 16:06
 */

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\IterableInterface;
use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Aropixel\AdminBundle\Domain\Menu\Model\RoutableInterface;
use Symfony\Component\Routing\RouterInterface;

class MenuMatcher
{
    private RouterInterface $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function matchActive(Menu $menu) : void
    {
        foreach ($menu->getItems() as $item) {

            if ($item instanceof RoutableInterface && $this->isActiveRoute($item)) {
                $item->setIsActive(true);
            }

            if ($item instanceof IterableInterface) {
                $this->matchActiveChildren($item);
            }

        }
    }


    private function matchActiveChildren(IterableInterface $iterable) : void
    {
        foreach ($iterable->getItems() as $item) {

            if ($item instanceof RoutableInterface && $this->isActiveRoute($item)) {
                $item->setIsActive(true);
            }

            if ($item instanceof IterableInterface) {
                $this->matchActiveChildren($item);
            }

        }
    }

    private function isActiveRoute(RoutableInterface $item) : bool
    {
        return $this->router->getContext()->getPathInfo() == $this->router->generate($item->getRouteName(), $item->getRouteParameters());
    }

}
