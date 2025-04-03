<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface, PasswordAuthenticatedUserInterface, PasswordHasherAwareInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function isEnabled(): bool;

    public function setEnabled(bool $boolean): void;

    public function isInitialized(): bool;

    public function setInitialized(bool $boolean): void;

    public function setPassword(string $password): void;

    public function getPlainPassword(): ?string;

    public function setPlainPassword(string $password): void;

    public function setLastLogin(?\DateTime $lastLogin): void;

    public function tooOldPassword(string $delay): bool;

    public function tooOldLastLogin(): bool;

    public function setLastPasswordUpdate(\DateTime $lastPasswordUpdate): void;

    public function getPasswordResetToken(): ?string;

    public function setPasswordResetToken(?string $passwordResetToken): void;

    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt): void;

    public function isPasswordRequestExpired(\DateInterval $ttl): bool;
}
