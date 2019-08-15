<?php

namespace Aropixel\AdminBundle\Menu;

use Aropixel\AdminBundle\Event\ConfigureMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MenuBuilder implements ContainerAwareInterface
{
    //
    use ContainerAwareTrait;

    //
    private $factory;


    public function mainMenu(FactoryInterface $factory)
    {
        //
        $menu = $factory->createItem('root');

        //
        $em = $this->container->get('doctrine')->getManager();
        $this->container->get('event_dispatcher')->dispatch(
            ConfigureMenuEvent::USERSMENU,
            new ConfigureMenuEvent($factory, $menu, $em)
        );

        //
        $this->reorderMenuItems($menu);

        //
        return $menu;
    }



    public function reorderMenuItems($menu)
    {

        $menuOrderArray = array();
        $addLast = array();
        $alreadyTaken = array();

        foreach ($menu->getChildren() as $key => $menuItem) {

            if ($menuItem->hasChildren()) {
                $this->reorderMenuItems($menuItem);
            }

            $orderNumber = $menuItem->getExtra('weight');

            if ($orderNumber != null) {
                if (!isset($menuOrderArray[$orderNumber])) {
                    $menuOrderArray[$orderNumber] = $menuItem->getName();
                } else {
                    $alreadyTaken[$orderNumber] = $menuItem->getName();
                    // $alreadyTaken[] = array('orderNumber' => $orderNumber, 'name' => $menuItem->getName());
                }
            } else {
                $addLast[] = $menuItem->getName();
            }
        }

        // sort them after first pass
        ksort($menuOrderArray);

        // handle position duplicates
        if (count($alreadyTaken)) {
            foreach ($alreadyTaken as $key => $value) {
                // the ever shifting target
                $keysArray = array_keys($menuOrderArray);

                $position = array_search($key, $keysArray);

                if ($position === false) {
                    continue;
                }

                $menuOrderArray = array_merge(array_slice($menuOrderArray, 0, $position), array($value), array_slice($menuOrderArray, $position));
            }
        }

        // sort them after second pass
        ksort($menuOrderArray);

        // add items without ordernumber to the end
        if (count($addLast)) {
            foreach ($addLast as $key => $value) {
                $menuOrderArray[] = $value;
            }
        }

        if (count($menuOrderArray)) {
            $menu->reorderChildren($menuOrderArray);
        }
    }

}
