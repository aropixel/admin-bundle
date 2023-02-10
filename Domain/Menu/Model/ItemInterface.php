<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 12:42
 */

namespace Aropixel\AdminBundle\Domain\Menu\Model;

interface ItemInterface
{
    public function getId(): ?string;
    public function setId(string $id);
    public function getLabel(): string;
    public function getType(): string;
    public function hasChildren(): bool;
    public function setIsActive(bool $isActive);
    public function isActive() : bool;
    public function setParent(IterableInterface $parent);
    public function getParent() : ?IterableInterface;
}
