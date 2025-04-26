<?php

namespace App\Tests\Service;

use App\Entity\Application;
use App\Entity\Depositary;
use App\Entity\Portfolio;
use App\Enums\ActionEnum;
use App\Repository\ApplicationRepository;
use App\Repository\DepositaryRepository;
use  App\Service\DealLogService;
use  App\Service\DealService;
use App\Entity\Stock;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;


class DealServiceTest extends TestCase
{
    private ApplicationRepository|MockObject $applicationRepository;
    private DepositaryRepository|MockObject $depositaryRepository;
    private DealLogService|MockObject $dealLogService;
    private DealService $dealService;

    protected function setUp(): void
    {
        $this->applicationRepository = $this->createMock(ApplicationRepository::class);
        $this->depositaryRepository = $this->createMock(DepositaryRepository::class);
        $this->dealLogService = $this->createMock(DealLogService::class);

        $this->dealService = new DealService(
            $this->applicationRepository,
            $this->depositaryRepository,
            $this->dealLogService
        );
    }

    /**
     * @dataProvider providerApplication
     */
    public function testExecuteDeal(Application $originalApplication , ?Application $appropriateApplication): void
    {
        $this->applicationRepository
        ->expects($this-> once())
        ->method('findAppropriate')
        ->with($originalApplication) 
        ->willReturn($appropriateApplication)
        ;
        if($appropriateApplication == null){
            $this->depositaryRepository->expects($this->never())
            ->method('removeDepositary');

            $this->applicationRepository -> expects($this->never())
            ->method('saveChanges');

            $this-> dealLogService -> expects($this->never())
            ->method('registerDealLog');

            $this-> applicationRepository ->expects($this->never())
            ->method('removeApplication')
            -> withConsecutive([$appropriateApplication,$originalApplication]);


        }
        else{
            $this->applicationRepository
            ->expects($this->once())
            ->method('saveChanges');
            $this->dealLogService->expects($this->once())
            ->method('registerDealLog')
            ->with($originalApplication, $appropriateApplication);

            $this->applicationRepository
            ->expects($this->exactly(2))
            ->method('removeApplication')
            ->withConsecutive([$originalApplication], [$appropriateApplication]);
           
        }
        $this->dealService->executeDeal($originalApplication);
    }


    public  function providerApplication(): array {
        return[

            "Не получили подходящей заявки" =>[
                (new Application()),
                null
            ],
            "Заявка на покупку и нашли заявку на продажу" =>[
                
           self::configureBuyApplication(10,10),
           self::configureSellApplication(10,10)
            ],
            "Заявка на продажу и нашли заявку на продажу" =>[

                self::configureSellApplication(10,10),
                self::configureBuyApplication(10,10),
     
                 ]
        ];
    }

    private  function configureBuyApplication(float $price, int $quantity): Application|MockObject{
        $buyApplication = self::createMock(Application::class);
        $buyApplication
            ->expects($this->atMost(1))
            ->method('getAction')
            ->willReturn(ActionEnum::BUY);

        $buyApplication
            ->expects($this->once())
            ->method('getPortfolio')
            ->willReturn($portfolio = self::createMock(Portfolio::class));
            
         $buyApplication 
        ->expects($this->exactly(2))
        ->method('getTotal')
        ->willReturn($price*$quantity);

        $buyApplication
            ->expects($this->exactly(1))
            ->method('getStock')
            ->willReturn($stock = self::createMock(Stock::class));

        $buyApplication
            ->expects($this->exactly(1))
            ->method('getQuantity')
            ->willReturn($quantity);

        $portfolio
            ->expects($this->once())
            ->method('subBalance')
            ->with($quantity*$price);

        $portfolio->expects($this->once())
            ->method('subFreezeBalance')
            ->with($quantity*$price);

        $portfolio
            ->expects($this->exactly(1))
            ->method('addDepositaryQuantityByStock')
            ->with($stock, $quantity);
        

        return $buyApplication;
    }


    private function configureSellApplication(float $price, int $quantity): Application|MockObject{

        $sellApplication = self::createMock(Application::class);
        $sellApplication
            ->expects($this->atMost(1))
            ->method('getAction')
            ->willReturn(ActionEnum::SELL);

        $sellApplication
            ->expects(self::once())
            ->method('getPortfolio')
            ->willReturn($portfolio = self::createMock(Portfolio::class));
        
        $sellApplication
            ->expects($this->once())
            ->method('getTotal')
            ->willReturn($price*$quantity);
        
        $sellApplication
            ->expects($this->once())
            ->method('getStock')
            ->willReturn($stock = self::createMock(Stock::class));
        
        $sellApplication
            ->expects($this->exactly(2))
            ->method('getQuantity')
            ->willReturn($quantity);
        
        $portfolio
            ->expects($this->once())
            ->method('getDepositaryByStock')
            ->with($stock)
            ->willReturn($depositary = self::createMock(Depositary::class));
        

        $portfolio -> expects($this->once())
            ->method('addBalance')
            ->with($quantity*$price); 
        
        $depositary
            ->expects($this->once())
            ->method('subQuantity')
            ->with($quantity);
        

        return $sellApplication;
    
    }

}
