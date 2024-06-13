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
use Aropixel\AdminBundle\Infrastructure\Menu\Renderer\MenuMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class MenuMatcher implements MenuMatcherInterface
{
    protected RequestStack $requestStack;
    protected RouterInterface $router;

    protected ?string $mustMatchRoute = null;
    protected array $mustMatchRouteParameters = [];

    /**
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }


    public function mustMatch(string $forceMatchRoute = null, array $forceMatchRouteParams = []): void
    {
        $this->mustMatchRoute = $forceMatchRoute;
        $this->mustMatchRouteParameters = $forceMatchRouteParams;
    }


    public function matchActive(Menu $menu) : void
    {
        foreach ($menu->getItems() as $item) {

            if ($item instanceof RoutableInterface && ($this->isSetRoute($item) || $this->isActiveRoute($item))) {
                $item->setIsActive(true);
            }

            if ($item instanceof IterableInterface) {
                $this->matchActiveChildren($item);
            }

        }
    }

    protected function isSetRoute(RoutableInterface $item) : bool
    {
        if (!strlen($item->getRouteName())) {
            return false;
        }

        return $item->getRouteName() == $this->mustMatchRoute &&
            $this->mustMatchRouteParameters == $item->getRouteParameters()
            ;
    }

    protected function matchActiveChildren(IterableInterface $iterable) : void
    {
        foreach ($iterable->getItems() as $item) {

            if ($item instanceof RoutableInterface&& ($this->isSetRoute($item) || $this->isActiveRoute($item))) {
                $item->setIsActive(true);
            }

            if ($item instanceof IterableInterface) {
                $this->matchActiveChildren($item);
            }

        }
    }

    protected function isActiveRoute(RoutableInterface $item, $ignoreParameters=['id']) : bool
    {
        if (!strlen($item->getRouteName())) {
            return false;
        }

        $isExactRoute = $this->compareRoute($item->getRouteName(), $item->getRouteParameters());
        if ($isExactRoute) {
            return true;
        }

        if ($this->isIndexRoute($item->getRouteName())) {

            $baseRoute = $this->getBaseCrud($item->getRouteName());

            $currentRouteName = $this->requestStack->getCurrentRequest()->get('_route');
            $currentRouteParameters = $this->requestStack->getCurrentRequest()->get('_route_params');

            foreach ($ignoreParameters as $ignoreParameter) {
                if (array_key_exists($ignoreParameter, $currentRouteParameters)) {
                    unset($currentRouteParameters[$ignoreParameter]);
                }
            }

            $menuRouteParameters = $item->getRouteParameters();
            foreach ($ignoreParameters as $ignoreParameter) {
                if (array_key_exists($ignoreParameter, $menuRouteParameters)) {
                    unset($menuRouteParameters[$ignoreParameter]);
                }
            }

            $extensions = array('_new', '_edit');
            foreach ($extensions as $extension) {
                $routeName = $baseRoute . $extension;
                if ($routeName == $currentRouteName && $menuRouteParameters == $currentRouteParameters) {
                    return true;
                }
            }


        }

        return false;

    }

    protected function compareRoute(string $routeName, array $routeParameters) : bool
    {
        return $this->router->getContext()->getPathInfo() == $this->router->generate($routeName, $routeParameters);
    }


    protected function isIndexRoute(string $routeName) {
        return (
            strpos($routeName, '_index') !== false
        );
    }


    protected function getBaseCrud(string $routeName) {
        $i = strrpos($routeName, '_');
        $racine = substr($routeName, 0, $i);

        return $racine;
    }

}
