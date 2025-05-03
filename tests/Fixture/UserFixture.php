<?php 

namespace App\Tests\Fixture;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UserFixture extends AbstractFixture{




public function  load(ObjectManager $manager):void {
        
    $userAdmin  = new User();
    $userAdmin -> setUsername('admin');
    $userAdmin -> setPassword('password');

    $userAdmin -> setRoles(['ROLE_ADMIN']); 

    $manager->persist($userAdmin);
    $manager -> flush();

    $this->addReference('user-admin', $userAdmin);

    }

}


