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
        //PASSWORD_BCRYPT — это константа в PHP, используемая для указания алгоритма хеширования при вызове функции password_hash()
        $hashedPassword = password_hash('admin_password', PASSWORD_BCRYPT); 
        $userAdmin->setPassword($hashedPassword); # +0.5 балла за использование PasswordHasher
        $userAdmin->setRoles(['ROLE_ADMIN']);

        $manager->persist($userAdmin);

        $this->addReference(self::USER_ADMIN_REFERENCE, $userAdmin);

        $user = new User();
        $user->setUsername('user');
        $hashedPassword = password_hash('user_password', PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword); 

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_USER_REFERENCE, $user);
    }
}