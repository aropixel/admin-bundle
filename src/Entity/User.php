<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'aropixel_admin_user')]
class User implements UserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = false;

    #[ORM\Column(type: 'boolean')]
    protected bool $initialized = false;

    #[ORM\Column(type: 'integer')]
    protected int $passwordAttempts = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $lastName = null;

    /** @var array<string>  */
    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    #[ORM\Column(type: 'string')]
    protected ?string $password = null;

    protected ?string $plainPassword = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $passwordResetToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $passwordRequestedAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $emailVerificationToken = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected ?\DateTime $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $lastPasswordUpdate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $lastLogin = null;

    #[ORM\OneToOne(targetEntity: UserImageInterface::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?UserImage $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(mixed $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(mixed $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_ADMIN
        $roles = $this->roles;
        $roles[] = self::ROLE_ADMIN;

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getCompleteName(): string
    {
        if ($this->firstName || $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }

        return $this->email;
    }

    public function isSuperAdmin(): bool
    {
        return \in_array(static::ROLE_SUPER_ADMIN, $this->roles);
    }

    public function setSuperAdmin(bool $boolean): self
    {
        if ($boolean) {
            $this->roles[] = self::ROLE_ADMIN;
            $this->roles[] = self::ROLE_SUPER_ADMIN;
        } else {
            $this->roles = [self::ROLE_ADMIN];
        }

        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): void
    {
        $this->plainPassword = $password;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $boolean): void
    {
        $this->enabled = $boolean;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function setInitialized(bool $isInitialized): void
    {
        $this->initialized = $isInitialized;
    }

    public function getPasswordAttempts(): int
    {
        return $this->passwordAttempts;
    }

    public function setPasswordAttempts(int $passwordAttempts): void
    {
        $this->passwordAttempts = $passwordAttempts;
    }

    public function tooOldPassword(string $delay): bool
    {
        $now = new \DateTime('now');
        $lastPasswordUpdate = $this->getLastPasswordUpdate() ?: $this->getCreatedAt();

        $lastPasswordUpdate = clone $lastPasswordUpdate;
        $lastPasswordUpdate = $lastPasswordUpdate->modify('+' . $delay);

        if ($now > $lastPasswordUpdate) {
            return true;
        }

        return false;
    }

    public function tooOldLastLogin(): bool
    {
        $lastLogin = $this->getLastLogin();

        $now = new \DateTime('now');

        if ($lastLogin) {
            $lastLoginAnd3Months = clone $lastLogin;
            $lastLoginAnd3Months = $lastLoginAnd3Months->modify('+3 month');
            $lastLoginAnd3Months = $lastLoginAnd3Months->modify('+1 day');

            if ($now > $lastLoginAnd3Months) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }


    public function setPasswordResetToken(?string $passwordResetToken): void
    {
        $this->passwordResetToken = $passwordResetToken;
    }

    public function isPasswordRequestExpired(\DateInterval $ttl): bool
    {
        if (null === $this->passwordRequestedAt) {
            return false;
        }

        $threshold = new \DateTime();
        $threshold->sub($ttl);

        return $threshold > $this->passwordRequestedAt;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    /**
     * @return string
     */
    public function getEmailVerificationToken()
    {
        return $this->emailVerificationToken;
    }

    /**
     * @param string $emailVerificationToken
     */
    public function setEmailVerificationToken($emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPasswordHasherName(): ?string
    {
        return 'harsh';
    }

    public function getLastPasswordUpdate(): ?\DateTime
    {
        return $this->lastPasswordUpdate;
    }

    public function setLastPasswordUpdate(\DateTime $lastPasswordUpdate): void
    {
        $this->lastPasswordUpdate = $lastPasswordUpdate;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getImage(): ?UserImage
    {
        return $this->image;
    }

    public function setImage(?UserImage $image): self
    {
        // unset the owning side of the relation if necessary
        if (null === $image && null !== $this->image) {
            $this->image->setUser(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $image && $image->getUser() !== $this) {
            $image->setUser($this);
        }

        $this->image = $image;

        return $this;
    }
}
