<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Symfony\Component\String\Slugger\AsciiSlugger;

class SubMenu implements ItemInterface, IterableInterface
{
    private array $items = [];

    private bool $isActive = false;

    private ?IterableInterface $parent = null;

    public function __construct(
        private readonly string $label,
        private array $properties,
        private ?string $id = null
    ) {
    }

    public function getType(): string
    {
        return 'submenu';
    }

    public function hasChildren(): bool
    {
        return \count($this->items);
    }

    public function getParent(): ?IterableInterface
    {
        return $this->parent;
    }

    public function setParent(?IterableInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty($property): string
    {
        return \array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return ItemInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(ItemInterface $item): void
    {
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));
        $item->setParent($this);
        $this->items[] = $item;
    }

    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
        if ($this->parent && $isActive) {
            $this->parent->setIsActive($isActive);
        }
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    private function generateId(string $label): string
    {
        $slugger = new AsciiSlugger();

        return mb_strtolower($slugger->slug($label));
    }
}
