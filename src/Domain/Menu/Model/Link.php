<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

class Link implements ItemInterface, RoutableInterface
{
    private ?string $externalLink = null;

    private ?ItemInterface $parent = null;

    private bool $isActive = false;

    /**
     * @param array<mixed> $routeParameters
     * @param array<string,string> $properties
     */
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

    public function getParent(): ?ItemInterface
    {
        return $this->parent;
    }

    public function setParent(?ItemInterface $parent): void
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

    /**
     * @return array<string,string>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getProperty(string $property): string
    {
        return \array_key_exists($property, $this->properties) ? $this->properties[$property] : '';
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

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }
}
