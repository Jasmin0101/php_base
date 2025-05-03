<?php

namespace App\Tests\Fixture;

use App\Entity\Portfolio;
use App\Entity\User;
use App\Enums\ActionEnum;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PortfolioFixture extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        // TODO: Implement load() method.
        $adminPortfolio = new Portfolio();
        $adminPortfolio->setBalance(100);
        $adminPortfolio -> setFreezeBalance(0);
        $adminPortfolio -> setUser($this->getReference('user-admin', User::class));

        $manager -> persist($adminPortfolio);
        $manager -> flush();

        $this->addReference('portfolio-admin', $adminPortfolio);


        
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}
