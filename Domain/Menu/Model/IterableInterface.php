<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 16:21
 */

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Aropixel\AdminBundle\Domain\Menu\Model\ItemInterface;

interface IterableInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems() : array;
}
