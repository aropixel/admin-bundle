<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 11:52
 */

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Aropixel\AdminBundle\Domain\Menu\Model\ItemInterface;
use Aropixel\AdminBundle\Domain\Menu\Model\IterableInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;


class Menu implements IterableInterface
{
    private string $id;
    private array $items = [];
    private array $positions = [];

    /**
     * @param string $id
     */
    public function __construct(string $id = "menu")
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    public function addItem(ItemInterface $item) : void
    {
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));
        $this->items[] = $item;
        $this->positions[] = $item->getId();
    }

    public function addBefore(string $id, ItemInterface $item) : void
    {
        $i = array_search($id, $this->positions);
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));

        if ($i === false) {
            $this->items[] = $item;
            $this->positions[] = $item->getId();
        }
        else {
            $i = max(0,  $i - 1);
            $this->items = array_merge(
                array_slice($this->items, 0, $i),
                array($item),
                array_slice($this->items, $i, count($this->items)-$i)
            );
            $this->positions = array_merge(
                array_slice($this->positions, 0, $i),
                array($item->getId()),
                array_slice($this->positions, $i, count($this->positions)-$i)
            );
        }

    }

    public function addAfter(string $id, ItemInterface $item) : void
    {
        $i = array_search($id, $this->positions);
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));

        if ($i === false) {
            $this->items[] = $item;
            $this->positions[] = $item->getId();
        }
        else {

            $i++;
            $this->items = array_merge(
                array_slice($this->items, 0, $i),
                array($item),
                array_slice($this->items, $i, count($this->items)-$i)
            );
            $this->positions = array_merge(
                array_slice($this->positions, 0, $i),
                array($item->getId()),
                array_slice($this->positions, $i, count($this->positions)-$i)
            );

        }
    }

    /**
     * @return ItemInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }


    private function generateId(string $label) : string
    {
        $i = 1;
        $slugger = new AsciiSlugger();
        $id = strtolower($slugger->slug($label));

        while (in_array($id, $this->positions)) {
            $suffixedLabel = $label.' '.$i++;
            $id = strtolower($slugger->slug($suffixedLabel));
        }

        return $id;
    }
}
