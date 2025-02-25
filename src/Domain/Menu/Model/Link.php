<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

class Link implements ItemInterface, RoutableInterface
{
    private ?string $externalLink = null;

    private ?IterableInterface $parent = null;

    private bool $isActive = false;

    public function __construct(
        private readonly string $label,
        private readonly string $routeName,
        private readonly array $routeParameters = [],
        private array $properties = [],
        private ?string $id = null
    ) {
    }

    public function getType(): string
    {
        return 'link';
    }

    public function getParent(): ?IterableInterface
    {
        return $this->parent;
    }

    public function setParent(?IterableInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function hasChildren(): bool
    {
        return false;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function getProperties(): array
    {
        return $this->properties;
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

    public function getProperty($property): string
    {
        return \array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
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

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }
}
