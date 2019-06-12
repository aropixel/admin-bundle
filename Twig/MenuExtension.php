<?php
namespace Aropixel\AdminBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\MenuFactory;
use Aropixel\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class MenuExtension extends AbstractExtension
{
    /** @var EventDispatcher */
    private $eventDispatcher;

    /** @var Router */
    private $router;

    /** @var EntityManagerInterface  */
    private $em;


    public function __construct(EventDispatcherInterface $eventDispatcher, RouterInterface $router, EntityManagerInterface $em)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->em = $em;
    }


    public function getFunctions()
    {
        return array(
            'app_menu' => new TwigFunction('app_menu', array($this, 'renderMenus'), array(
                'is_safe' => array('html'),
                'needs_environment' => true
            ))
        );
    }

    public function getName()
    {
        return 'menu';
    }



    public function renderMenus(Environment $twig)
    {
        //
        $app_menu = array();
        $factory = new MenuFactory();
        $factory->addExtension(new \Knp\Menu\Integration\Symfony\RoutingExtension($this->router), 10);
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-bar');

        //
        $event = new ConfigureMenuEvent($factory, $menu, $this->em, $app_menu);
        $this->eventDispatcher->dispatch(
            ConfigureMenuEvent::APPMENU,
            $event
        );



        // $renderer = new \Knp\Menu\Renderer\ListRenderer($itemMatcher);
        $itemMatcher = new \Knp\Menu\Matcher\Matcher();
        $menuRenderer = new \Knp\Menu\Renderer\TwigRenderer($twig, '@AropixelAdmin/Menu/menu.html.twig', $itemMatcher, array(
            'currentClass' => 'active'
        ));
        // $listrenderer = new \Knp\Menu\Renderer\ListRenderer($itemMatcher);

        $html = "";
        foreach ($event->getAppMenu() as $t_menu) {

            if ($t_menu['section']) {
                $html.= $twig->render('@AropixelAdmin/Menu/section.html.twig', array('title' => $t_menu['section']));
            }

            $html.= $menuRenderer->render($this->reorderMenuItems($t_menu['menu']));

        }

        return $html;

    }

    private function reorderMenuItems($menu)
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

        return $menu;
    }
}
