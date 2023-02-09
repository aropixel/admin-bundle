<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 12:39
 */

namespace Aropixel\AdminBundle\Domain\Menu\Model;

class Link implements ItemInterface, RoutableInterface
{
    private string $label;

    private string $routeName;

    private array $routeParameters;

    private array $properties;

    private ?string $id;

    private ?IterableInterface $parent = null;

    private bool $isActive = false;

    /**
     * @param string $label
     * @param string $routeName
     * @param array $routeParameters
     * @param array $properties
     * @param string|null $id
     */
    public function __construct(string $label, string $routeName, array $routeParameters=[], array $properties=[], ?string $id=null)
    {
        $this->label = $label;
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
        $this->properties = $properties;
        $this->id = $id;
    }

    public function getType(): string
    {
        return 'link';
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

    public function hasChildren(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
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

    public function getProperty($property) : string
    {
        return array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
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

}
