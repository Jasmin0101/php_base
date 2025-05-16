<?php

namespace App\Tests\Fixture;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends AbstractFixture
{
    public const USER_ADMIN_REFERENCE = 'user-admin';
    public const USER_USER_REFERENCE = 'user-user';
    public function load(ObjectManager $manager): void
    {
        $userAdmin = new User();
        $userAdmin->setUsername('admin');
        $userAdmin->setPassword('admin_password'); # +0.5 балла за использование PasswordHasher
        $userAdmin->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAdmin);

        $this->addReference(self::USER_ADMIN_REFERENCE, $userAdmin);

        $user = new User();
        $user->setUsername('user');
        $user->setPassword('user_password');

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_USER_REFERENCE, $user);
    }
}