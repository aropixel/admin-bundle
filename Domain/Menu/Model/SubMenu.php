<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 14:23
 */

namespace Aropixel\AdminBundle\Domain\Menu\Model;

class SubMenu implements ItemInterface, IterableInterface
{
    private string $label;

    private array $properties;

    private array $items = [];

    private ?string $id;

    private bool $isActive = false;

    private ?IterableInterface $parent = null;


    /**
     * @param string $label
     * @param array $properties
     * @param string|null $id
     */
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

    /**
     * @return IterableInterface|null
     */
    public function getParent(): ?IterableInterface
    {
        return $this->parent;
    }

    /**
     * @param IterableInterface|null $parent
     */
    public function setParent(?IterableInterface $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty($property) : string
    {
        return array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
    }

    /**
     * @return string|null
     */
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


    public function addItem(ItemInterface $item) : void
    {
        $item->setParent($this);
        $this->items[] = $item;
    }

    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

}
