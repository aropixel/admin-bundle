<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Symfony\Component\String\Slugger\AsciiSlugger;

class SubMenu implements ItemInterface, IterableInterface
{
    private string $label;

    private array $properties;

    private array $items = [];

    private ?string $id;

    private bool $isActive = false;

    private ?IterableInterface $parent = null;

    private ?Link $defaultChild = null;


    public function __construct(string $label, array $properties, ?string $id=null)
    {
        $this->label = $label;
        $this->properties = $properties;
        $this->id = $id;
    }

    public function getType(): string
    {
        return 'submenu';
    }

    public function hasChildren(): bool
    {
        return count($this->items);
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

    public function getProperty($property) : string
    {
        return array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(ItemInterface $item) : void
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

    private function generateId(string $label) : string
    {
        $slugger = new AsciiSlugger();
        return strtolower($slugger->slug($label));
    }

    public function getDefaultChild(): ?Link
    {
        return $this->defaultChild;
    }

    public function setDefaultChild(?Link $defaultChild): void
    {
        $this->defaultChild = $defaultChild;
    }

}
