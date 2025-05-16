<?php

namespace App\Tests\Fixture;

use App\Entity\Portfolio;
use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PortfolioFixture extends AbstractFixture implements DependentFixtureInterface
{
    public const PORTFOLIO_ADMIN_REFERENCE = 'portfolio-admin';
    public const PORTFOLIO_USER_REFERENCE = 'portfolio-user';
    public function load(ObjectManager $manager): void
    {
        $adminPortfolio = new Portfolio();
        $adminPortfolio->setBalance(100);
        $adminPortfolio->setFreezeBalance(0);
        $adminPortfolio->setUser($this->getReference(UserFixture::USER_ADMIN_REFERENCE, User::class));

        $manager->persist($adminPortfolio);

        $this->addReference(self::PORTFOLIO_ADMIN_REFERENCE, $adminPortfolio);

        $userPortfolio = new Portfolio();
        $userPortfolio->setBalance(100);
        $userPortfolio->setFreezeBalance(0);
        $userPortfolio->setUser($this->getReference(UserFixture::USER_USER_REFERENCE, User::class));
        $manager->persist($userPortfolio);

        $this->addReference(self::PORTFOLIO_USER_REFERENCE, $userPortfolio);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}