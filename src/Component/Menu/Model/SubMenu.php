<?php

namespace Aropixel\AdminBundle\Component\Menu\Model;

use Symfony\Component\String\Slugger\AsciiSlugger;

class SubMenu implements ItemInterface, IterableInterface
{
    /**
     * @var ItemInterface[]
     */
    private array $items = [];

    private bool $isActive = false;

    private ?ItemInterface $parent = null;
    private ?Link $defaultChild = null;

    /**
     * @param array<string,string> $properties
     */
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
        return \count($this->items) > 0;
    }

    public function getParent(): ?ItemInterface
    {
        return $this->parent;
    }

    public function setParent(?ItemInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<string,string>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $property): string
    {
        return \array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
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

    public function addItem(ItemInterface $item): void
    {
        $item->setId($item->getId() ?: $this->generateId($item->getLabel()));
        $item->setParent($this);
        $this->items[] = $item;
    }

    public function setIsActive(bool $isActive): void
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

    public function getDefaultChild(): ?Link
    {
        return $this->defaultChild;
    }

    public function setDefaultChild(?Link $defaultChild): void
    {
        $this->defaultChild = $defaultChild;
    }

    private function generateId(string $label): string
    {
        $slugger = new AsciiSlugger();

        return mb_strtolower($slugger->slug($label));
    }
}
