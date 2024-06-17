<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserImageInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
#[ORM\Table(name: "aropixel_admin_user_image")]
class UserImage extends AttachedImage implements UserImageInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    protected ?int $id = null;

    #[ORM\OneToOne(inversedBy: "image", targetEntity: User::class, cascade: ["persist", "remove"])]
    protected ?User $user = null;

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
