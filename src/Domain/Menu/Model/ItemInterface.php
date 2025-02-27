<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

interface ItemInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getLabel(): string;

    public function getType(): string;

    public function hasChildren(): bool;

    public function setIsActive(bool $isActive): void;

    public function isActive(): bool;

    public function setParent(ItemInterface $parent): void;

    public function getParent(): ?ItemInterface;
}
