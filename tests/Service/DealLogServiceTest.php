<?php

namespace App\Tests;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Enums\ActionEnum;
use App\Entity\Depositary;
use App\Repository\DealLogRepository;
use App\Service\DealLogService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;


class DealLogServiceTest extends TestCase
{
    private DealLogRepository|MockObject $dealLogRepository;
    private DealLogService $dealLogService;

    protected function setUp(): void
    {
        $this->dealLogRepository = $this->createMock(DealLogRepository::class);
        $this->dealLogService = new DealLogService($this->dealLogRepository);
    }

    /**
     * @dataProvider providerDealLogService
     */
    public function testRegisterDealLog(
        float $price,
        int $quantity
    ): void {
        // Arrange
        $buyApplication = $this->createMock(Application::class);
        $sellApplication = $this->createMock(Application::class);

        $stock = $this->createMock(Stock::class);
        $buyPortfolio = $this->createMock(Portfolio::class);
        $sellPortfolio = $this->createMock(Portfolio::class);

        $buyApplication->method('getAction')->willReturn(ActionEnum::BUY);
        $buyApplication->method('getStock')->willReturn($stock);
        $buyApplication->method('getPrice')->willReturn($price);
        $buyApplication->method('getPortfolio')->willReturn($buyPortfolio);
        $buyApplication->method('getQuantity')->willReturn($quantity);

        $sellApplication->method('getPortfolio')->willReturn($sellPortfolio);

        // Ожидаем, что репозиторий получит вызов с объектом DealLog
        $this->dealLogRepository
            ->expects($this->once())
            ->method('saveDealLog')
            ->with($this->isInstanceOf(DealLog::class));

        // Act
        $dealLog = $this->dealLogService->registerDealLog($buyApplication, $sellApplication);

        // Assert
        $this->assertInstanceOf(DealLog::class, $dealLog); //Проверяет, что объект — экземпляр класса
        $this->assertSame($stock, $dealLog->getStock());//Проверяет, что значения равны и одного типа (===)
        $this->assertSame($buyPortfolio, $dealLog->getBuyPortfolio());
        $this->assertSame($sellPortfolio, $dealLog->getSellPortfolio());
        $this->assertEquals($price, $dealLog->getPrice());
        $this->assertEquals($quantity, $dealLog->getQuantity());
    }
    /**
     * @dataProvider provideDeltaCases
     */

    public function testCalculateDelta(
    float $buyPrice,
    int $buyQuantity,
    float $sellPrice,
    int $sellQuantity,
    float $latestPrice,
    float $expectedAbsolute,
    float $expectedPercent
    ): void {
        $buyApplication = $this->createMock(Application::class);
        $sellApplication = $this->createMock(Application::class);

        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $stock = $this->createMock(Stock::class);

        $stock->method('getId')->willReturn(1);
        $depositary->method('getPortfolio')->willReturn($portfolio);
        $depositary->method('getStock')->willReturn($stock);

        
    $buyDealLog = $this->createConfiguredMock(DealLog::class, [
        'getStock' => $stock,
        'getPrice' => $buyPrice,
        'getQuantity' => $buyQuantity,
    ]);

    $sellDealLog = $this->createConfiguredMock(DealLog::class, [
        'getStock' => $stock,
        'getPrice' => $sellPrice,
        'getQuantity' => $sellQuantity,
    ]);

    $latestDealLog = $this->createConfiguredMock(DealLog::class, [
        'getPrice' => $latestPrice
    ]);

        $buyDealLogs = new ArrayCollection([$buyDealLog]);
        $sellDealLogs = new ArrayCollection([$sellDealLog]);

        $portfolio->method('getBuyDealLogs')->willReturn($buyDealLogs);
        $portfolio->method('getSellDealLogs')->willReturn($sellDealLogs);

        $this->dealLogRepository
            ->method('findLatestByStock')
            ->with($stock)
            ->willReturn($latestDealLog);

        $delta = $this->dealLogService->calculateDelta($depositary);

        $this->assertEquals($expectedAbsolute, $delta->getAbsolute());
        $this->assertEquals($expectedPercent, $delta->getPercentage());

    $this->assertInstanceOf(\App\Entity\Delta::class, $delta);
    $this->assertEqualsWithDelta($expectedAbsolute, $delta->getAbsolute(), 0.01);
    $this->assertEqualsWithDelta($expectedPercent, $delta->getPercentage(), 0.01); //Проверяет равенство с погрешностью


    }

    public static function providerDealLogService(): array
    {
        return [
            'Тест 1: ' => [100.0, 10],
            'Тест 2:' => [0.0, 0],
            'Тест 3:' => [55.5, 1000],
        ];
    }

    public static function provideDeltaCases(): array
{
    return [
        'standard case' => [
            'buyPrice' => 100.0,
            'buyQuantity' => 5,
            'sellPrice' => 110.0,
            'sellQuantity' => 2,
            'latestPrice' => 120.0,
            'expectedAbsolute' => 80.0,      // actualSum - investSum = 360 - 280
            'expectedPercent' => 28.57       // (80 / 280) * 100
        ],
        'no sales yet' => [
            'buyPrice' => 50.0,
            'buyQuantity' => 10,
            'sellPrice' => 0.0,
            'sellQuantity' => 0,
            'latestPrice' => 60.0,
            'expectedAbsolute' => 100.0,     // (10 * 60) - (10 * 50)
            'expectedPercent' => 20.0
        ],
        'loss case' => [
            'buyPrice' => 200.0,
            'buyQuantity' => 3,
            'sellPrice' => 0.0,
            'sellQuantity' => 0,
            'latestPrice' => 150.0,
            'expectedAbsolute' => -150.0,    // (3 * 150) - (3 * 200)
            'expectedPercent' => -25.0
        ]
    ];
}

}
