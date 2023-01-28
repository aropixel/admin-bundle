<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Admin user for AropixelAdminBundle
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, PasswordHasherAwareInterface
{

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    protected ?int $id = null;

    protected ?string $email = null;

    protected bool $enabled = false;

    protected int $passwordAttempts = 0;

    protected ?string $firstName = null;

    protected ?string $lastName = null;

    protected array $roles = [];

    protected ?string $password = null;

    protected ?string $plainPassword = null;

    protected ?string $passwordResetToken = null;

    protected ?\DateTime $passwordRequestedAt = null;

    protected ?string $emailVerificationToken = null;

    protected ?\DateTime $createdAt = null;

    protected ?\DateTime $lastPasswordUpdate = null;

    protected ?\DateTime $lastLogin = null;

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

    /**
     * @return mixed
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return self
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return self
     */
    public function setLastName($lastName): self
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
        return $this->firstName.' '.$this->lastName;
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getCompleteName()
    {
        if ($this->firstName || $this->lastName) {
            return $this->firstName." ".$this->lastName;
        }
        else {
            return $this->email;
        }
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getSuperAdmin()
    {
        return in_array(static::ROLE_SUPER_ADMIN, $this->roles);
    }

    /**
     * {@inheritdoc}
     */
    public function setSuperAdmin($boolean)
    {
        if ($boolean) {
            $this->roles[] = self::ROLE_ADMIN;
            $this->roles[] = self::ROLE_SUPER_ADMIN;
        } else {
            $this->roles = array(self::ROLE_ADMIN);
        }

        $this->roles = array_unique($this->roles);
        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }


    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }

    /**
     * @return int
     */
    public function getPasswordAttempts(): int
    {
        return $this->passwordAttempts;
    }

    /**
     * @param int $passwordAttempts
     */
    public function setPasswordAttempts(int $passwordAttempts): void
    {
        $this->passwordAttempts = $passwordAttempts;
    }

    public function tooOldLastLogin()
    {
        $lastLogin = $this->getLastLogin();

        $now = new \Datetime('now');

        if ($lastLogin) {
            $lastLoginAnd3Months = clone($lastLogin);
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
    public function getPasswordResetToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * @param string $passwordResetToken
     * @return User
     */
    public function setPasswordResetToken($passwordResetToken): self
    {
        $this->passwordResetToken = $passwordResetToken;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired(\DateInterval $ttl): bool
    {
        if (null === $this->passwordRequestedAt) {
            return false;
        }

        $threshold = new \DateTime();
        $threshold->sub($ttl);

        return $threshold <= $this->passwordRequestedAt;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param \DateTime $passwordRequestedAt
     * @return User
     */
    public function setPasswordRequestedAt($passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
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
     * @return User
     */
    public function setEmailVerificationToken($emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;
        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
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

    /**
     * @return mixed
     */
    public function getLastPasswordUpdate(): ?\DateTime
    {
        return $this->lastPasswordUpdate;
    }

    /**
     * @param \DateTime $lastPasswordUpdate
     */
    public function setLastPasswordUpdate(\DateTime $lastPasswordUpdate): void
    {
        $this->lastPasswordUpdate = $lastPasswordUpdate;
    }

    /**
     * @return mixed
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param ?\DateTime $lastLogin
     */
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
        if ($image === null && $this->image !== null) {
            $this->image->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($image !== null && $image->getUser() !== $this) {
            $image->setUser($this);
        }

        $this->image = $image;

        return $this;
    }

}


