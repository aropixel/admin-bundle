<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 17/08/2023 à 15:07
 */

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuMatcherInterface
{
    public function matchActive(Menu $menu) : void;
}
