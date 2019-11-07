<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 01/04/2019 à 16:18
 */

namespace Aropixel\AdminBundle\Menu;


use Aropixel\AdminBundle\Event\ConfigureMenuEvent;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;



abstract class AbstractMenuListener
{

    /** @var  FactoryInterface */
    protected $factory;
    protected $requestStack;
    protected $authorizationChecker;

    /** @var EntityManagerInterface  */
    protected $em;


    protected $weight = 100;
    protected $routeName = "";
    protected $routeParameters = array();

    /** @var  ItemInterface */
    protected $menu;


    public $rootClass = 'sidebar-menu navbar-nav';
    public $catClass = 'menu-header';
    public $liClass = 'nav-item';
    public $aClass = 'nav-link';


    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }



    abstract public function onMenuConfigure(ConfigureMenuEvent $event);




    protected function createRoot() {

        /** @var  ItemInterface */
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', $this->rootClass);

        return $menu;

    }




    protected function addCategory($name) {

        if ($this->weight != 100) {
            $rest = ($this->weight % 100);
            $this->weight = $this->weight + (100 - $rest);
        }

        /** @var ItemInterface $item */
        $item = $this->factory->createItem($name);
        $item->setAttribute('class', $this->catClass);
        $item->setExtra('weight', $this->weight++);
        $this->menu->addChild($item);

        return $item;

    }


    protected function isActiveRoute($route) {

        //
        if (!is_array($route)) {
            $routeName = $route;
            $route = array('route' => $routeName, 'routeParameters' => array());
        }
        else if (!array_key_exists('routeParameters', $route)) {
            $route['routeParameters'] = array();
        }
        //
        $isExactRoute = ($route['route'] == $this->routeName && $route['routeParameters'] == $this->routeParameters);
        if ($isExactRoute) {
            return true;
        }

        //
        if ($this->isCrudIndex($route)) {

            $baseRoute = $this->getBaseCrud($route);

            //
            $activeRouteParameters = $this->routeParameters;
            if (array_key_exists('id', $activeRouteParameters)) {
                unset($activeRouteParameters['id']);
            }

            //
            $menuRouteParameters = $route['routeParameters'];
            if (array_key_exists('id', $menuRouteParameters)) {
                unset($menuRouteParameters['id']);
            }

            $extensions = array('_index', '_new', '_edit');
            foreach ($extensions as $extension) {
                $routeName = $baseRoute . $extension;
                if ($routeName == $this->routeName && $menuRouteParameters == $activeRouteParameters) {
                    return true;
                }
            }


        }

        return false;

    }


    protected function isCrudIndex($route) {

        return (
            strpos($route['route'], '_index') !== false
        );

//        return (
//            strpos($route['route'], '_index') !== false ||
//            strpos($route['route'], '_new') !== false ||
//            strpos($route['route'], '_edit') !== false ||
//            strpos($route['route'], '_order') !== false
//        );

    }


    protected function getBaseCrud($route) {

        $routeName = $route['route'];
        $i = strrpos($routeName, '_');
        $racine = substr($routeName, 0, $i);

        return $racine;
    }



    protected function createItem($name, $route="", $icon="", $validRoots=array(), $weight=null) {

        //
        $options = array();
        if (!is_array($route)) {
            $routeName = $route;
            $route = array('route' => $routeName);
        }

        $options = array_merge($options, $route);
        $options['label'] = $name;

        //
        if (!is_null($weight)) {
            $this->weight = $weight;
        }

        // Crée l'élément
        $item = $this->factory->createItem($name.'_'.$this->weight, $options);
        $item->setExtra('weight', $this->weight);
        $item->setAttribute('class', $this->liClass);
        $item->setLinkAttribute('class', $this->aClass);
        if (strlen($icon)) {
            $item->setExtra('icon', $icon);
        }
        $this->weight++;

        // Si la base de la route en cours est la même que la base de la route du menu
        // l'élement de menu est highlighté


        // Si on est sur une route spéciale (tableau de bord, ou route sans _
        // On prend la route comme racine
//        if (substr($routeName, 0, 1)=="_" || strpos($routeName, '_')===false) {
//            $racine = $routeName;
//        }
//        else {
//            $i = strrpos($routeName, '_');
//            $racine = substr($routeName, 0, $i);
//        }

//        if (strpos($this->routeName, $racine)===0) {
        $isCurrent = $this->isActiveRoute($route);
        if ($isCurrent) {
            $item->setCurrent(true);
        }
//        }


        return $item;

    }


    protected function addItem($name, $route="", $icon="", $validRoots=array(), $weight=null) {

        $item = $this->createItem($name, $route, $icon, $validRoots, $weight);
        $this->menu->addChild($item);

        return $item;
    }


    protected function createGroupItem($name, $icon="") {

        $item = $this->factory->createItem($name.$this->weight, array('label' => $name));
        $item->setAttribute('class', $this->liClass);
        $item->setLinkAttribute('class', $this->aClass);
        $item->setUri('#');
        $item->setExtra('weight', $this->weight++);
        if (strlen($icon)) {
            $item->setExtra('icon', $icon);
        }

        return $item;
    }


    protected function addSubItem(ItemInterface $groupItem, $name, $route="", $icon="") {

        $item = $this->createItem($name, $route, $icon);
        $item->setAttribute('class', $this->liClass);
        $item->setLinkAttribute('class', $this->aClass);
        $groupItem->addChild($item);

    }



    protected function addGroupItem(ItemInterface $groupItem, ItemInterface $parent=null) {

        $isGroupActive = false;
        $children = $groupItem->getChildren();

        // Parcours les enfants du groupe
        // pour vérifier si le groupe doit avoir l'état actif
        foreach ($children as $child) {
            $route = $child->getExtra('routes');

            $childRoute = $route[0];

            $isElementActive = false;
            if (is_array($childRoute)) {
                $childRoute['routeParameters'] = $childRoute['parameters'];
                $isElementActive = $this->isActiveRoute($childRoute);
            }

            $isGroupActive = ($isGroupActive || $isElementActive);
        }

        if ($isGroupActive) {
            $groupItem->setCurrent(true);
        }

        if ($parent) {
            $parent->addChild($groupItem);
        }
        else {
            $this->menu->addChild($groupItem);
        }

    }

}
