<?php


namespace Aropixel\AdminBundle\Security\Exception;


use Aropixel\AdminBundle\Entity\User;

class TooOldPasswordAuthenticationException extends \Exception
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Le mot de passe doit être renouvelé.';
    }

    public function getUser()
    {
        return $this->user;
    }
}
