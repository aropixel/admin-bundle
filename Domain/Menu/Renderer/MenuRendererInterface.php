<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 12:06
 */

namespace Aropixel\AdminBundle\Domain\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuRendererInterface
{
    public function renderMenu(Menu $menu) : string;
}
