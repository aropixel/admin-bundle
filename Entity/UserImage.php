<?php

namespace Aropixel\AdminBundle\Entity;

class UserImage extends AttachImage implements UserImageInterface
{
    private ?int $id = null;

    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
