<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 11:51
 */

namespace Aropixel\AdminBundle\Domain\Menu\Builder;


use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuBuilderInterface
{
    public function buildMenu() : Menu;
}
