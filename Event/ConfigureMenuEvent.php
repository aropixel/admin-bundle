<?php
// src/AropixelAdminBundle/Event/ConfigureMenuEvent.php

namespace Aropixel\AdminBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityManager;

class ConfigureMenuEvent extends Event
{
    const USERSMENU = 'aropixel_admin.users_menu_configure';
    const APPMENU = 'aropixel.admin_menu_configure';

    private $em;
    private $factory;
    private $menu;
    private $app_menu;

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Knp\Menu\ItemInterface $menu
     */
    public function __construct(FactoryInterface $factory, ItemInterface $menu, EntityManagerInterface $em, $app_menu=array())
    {
        $this->em = $em;
        $this->factory = $factory;
        $this->menu = $menu;
        $this->app_menu = $app_menu;
    }

    /**
     * @return \Knp\Menu\FactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function addAppMenu($menu, $section=false, $id=false)
    {
        $found = false;
        if ($id) {
            foreach ($this->app_menu as $i => $t_properties) {
                if (array_key_exists('id', $t_properties) && $t_properties['id']==$id) {
                    $this->app_menu[$i] = array('menu' => $menu, 'section' => $section, 'id' => $id);
                    $found = true;
                    break;
                }
            }

        }

        if (!$found || !$id)
        {

            $this->app_menu[] = array('menu' => $menu, 'section' => $section, 'id' => $id);

        }

        return $this->app_menu;
    }


    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getAppMenu($id=false)
    {
        if (!$id) {
            return $this->app_menu;
        }
        else {
            foreach ($this->app_menu as $t_properties) {
                if (array_key_exists('id', $t_properties) && $t_properties['id']==$id) {
                    return $t_properties['menu'];
                }
            }
        }

        return false;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }


}
