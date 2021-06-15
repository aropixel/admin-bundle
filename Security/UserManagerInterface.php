<?php
/**
 * Créé par Aropixel @2021.
 * Par: Joël Gomez Caballe
 * Date: 08/01/2021 à 12:19
 */

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\User;

interface UserManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRepository();

    /**
     * {@inheritdoc}
     */
    public function createUser();

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email);

    /**
     * {@inheritdoc}
     */
    public function updatePassword(User $user);

    /**
     * {@inheritdoc}
     */
    public function updateUser(User $user, $andFlush = true);
}
