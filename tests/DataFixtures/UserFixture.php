<?php

namespace Aropixel\AdminBundle\Tests\DataFixtures;

use Aropixel\AdminBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            ['email' => 'admin@example.com', 'password' => 'admin', 'superAdmin' => false],
            ['email' => 'superadmin@example.com', 'password' => 'admin', 'superAdmin' => true],
        ];

        foreach ($users as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setEnabled(true);
            $user->setInitialized(true);
            $user->setSuperAdmin($data['superAdmin']);
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
