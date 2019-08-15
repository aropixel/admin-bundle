<?php
namespace Aropixel\AdminBundle\Twig;
use Knp\Menu\MenuFactory;
use Aropixel\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Form\FormView;

class ParamsExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

    private $app_client;
    private $app_copyright;
    private $app_theme;


    public function __construct($admin_client, $admin_copyright, $admin_theme)
    {
        $this->app_client  = $admin_client;
        $this->app_copyright  = $admin_copyright;
        $this->app_theme = $admin_theme;
    }

    public function getGlobals()
    {
        return array(
            'app_client'  => $this->app_client,
            'app_copyright'  => $this->app_copyright,
            'app_theme' => $this->app_theme,
        );
    }

    public function getName()
    {
        return 'params';
    }

}
