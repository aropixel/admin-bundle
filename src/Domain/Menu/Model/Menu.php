<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Menu implements IterableInterface
{
    private array $items = [];
    private array $positions = [];

    public function __construct(
        private readonly string $id = 'menu',
        private readonly string $label = 'Administration'
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function addItem(ItemInterface $item): void
    {
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));
        $this->items[] = $item;
        $this->positions[] = $item->getId();
    }

    public function addBefore(string $id, ItemInterface $item): void
    {
        $i = array_search($id, $this->positions);
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));

        if (false === $i) {
            $this->items[] = $item;
            $this->positions[] = $item->getId();
        } else {
            $i = max(0, $i - 1);
            $this->items = array_merge(
                \array_slice($this->items, 0, $i),
                [$item],
                \array_slice($this->items, $i, \count($this->items) - $i)
            );
            $this->positions = array_merge(
                \array_slice($this->positions, 0, $i),
                [$item->getId()],
                \array_slice($this->positions, $i, \count($this->positions) - $i)
            );
        }
    }

    public function addAfter(string $id, ItemInterface $item): void
    {
        $i = array_search($id, $this->positions);
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));

        if (false === $i) {
            $this->items[] = $item;
            $this->positions[] = $item->getId();
        } else {
            ++$i;
            $this->items = array_merge(
                \array_slice($this->items, 0, $i),
                [$item],
                \array_slice($this->items, $i, \count($this->items) - $i)
            );
            $this->positions = array_merge(
                \array_slice($this->positions, 0, $i),
                [$item->getId()],
                \array_slice($this->positions, $i, \count($this->positions) - $i)
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

    private function generateId(string $label): string
    {
        $i = 1;
        $slugger = new AsciiSlugger();
        $id = mb_strtolower($slugger->slug($label));

        while (\in_array($id, $this->positions)) {
            $suffixedLabel = $label . ' ' . $i++;
            $id = mb_strtolower($slugger->slug($suffixedLabel));
        }

        return $id;
    }
}
