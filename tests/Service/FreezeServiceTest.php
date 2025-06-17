<?php

namespace App\Tests\Service;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Entity\Depositary;
use App\Enums\ActionEnum;
use App\Service\FreezeService;
use PHPUnit\Framework\TestCase;

class FreezeServiceTest extends TestCase
{
    private FreezeService $freezeService;

    protected function setUp(): void
    {
        $this->freezeService = new FreezeService();
    }

    /**
     * @dataProvider provideFreezeSellCases
     */
    public function testFreezeByApplicationSell(int $quantity): void
    {
        $stock = $this->createMock(Stock::class);
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::SELL);
        $application->method('getStock')->willReturn($stock);
        $application->method('getQuantity')->willReturn($quantity);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);
        $depositary->expects($this->once())->method('addFreezeQuantity')->with($quantity);

        $this->freezeService->freezeByApplication($application);
    }

    public static function provideFreezeSellCases(): array
    {
        return [
            'freeze 5 units' => [5],
            'freeze 10 units' => [10],
        ];
    }

    /**
     * @dataProvider provideFreezeBuyCases
     */
    public function testFreezeByApplicationBuy(float $total): void
    {
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::BUY);
        $application->method('getTotal')->willReturn($total);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->expects($this->once())->method('addFreezeBalance')->with($total);

        $this->freezeService->freezeByApplication($application);
    }

    public static function provideFreezeBuyCases(): array
    {
        return [
            'freeze 1000.0 balance' => [1000.0],
            'freeze 250.5 balance' => [250.5],
        ];
    }

    /**
     * @dataProvider provideUpdateSellCases
     */
    public function testUpdateFreezeByApplicationSell(int $oldQuantity, int $newQuantity): void
    {
        $stock = $this->createMock(Stock::class);
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::SELL);
        $application->method('getStock')->willReturn($stock);
        $application->method('getQuantity')->willReturn($newQuantity);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);

        $depositary->expects($this->once())->method('subFreezeQuantity')->with($oldQuantity)->willReturnSelf();
        $depositary->expects($this->once())->method('addFreezeQuantity')->with($newQuantity);

        $this->freezeService->updateFreezeByApplication($application, $oldQuantity, $newQuantity);
    }

    public static function provideUpdateSellCases(): array
    {
        return [
            'update from 5 to 8 units' => [5, 8],
            'update from 10 to 4 units' => [10, 4],
        ];
    }

    /**
     * @dataProvider provideUpdateBuyCases
     */
    public function testUpdateFreezeByApplicationBuy(int $oldQuantity, float $oldPrice, float $newTotal): void
    {
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::BUY);
        $application->method('getTotal')->willReturn($newTotal);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->expects($this->once())
            ->method('subFreezeBalance')
            ->with($oldPrice * $oldQuantity)
            ->willReturnSelf();

        $portfolio->expects($this->once())
            ->method('addFreezeBalance')
            ->with($newTotal);

        $this->freezeService->updateFreezeByApplication($application, $oldQuantity, $oldPrice);
    }

    public static function provideUpdateBuyCases(): array
    {
        return [
            'update from 2x100 to 300' => [2, 100.0, 300.0],
            'update from 4x50 to 280' => [4, 50.0, 280.0],
        ];
    }

    /**
     * @dataProvider provideUnfreezeSellCases
     */
    public function testUnfreezeByApplicationSell(int $quantity): void
    {
        $stock = $this->createMock(Stock::class);
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);
    // Trait 
        $application->method('getAction')->willReturn(ActionEnum::SELL);
        $application->method('getStock')->willReturn($stock);
        $application->method('getQuantity')->willReturn($quantity);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);
        $depositary->expects($this->once())->method('subFreezeQuantity')->with($quantity);

        $this->freezeService->unfreezeByApplication($application);
    }

    public static function provideUnfreezeSellCases(): array
    {
        return [
            'unfreeze 3 units' => [3],
            'unfreeze 7 units' => [7],
        ];
    }

    /**
     * @dataProvider provideUnfreezeBuyCases
     */
    public function testUnfreezeByApplicationBuy(float $total): void
    {
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::BUY);
        $application->method('getTotal')->willReturn($total);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->expects($this->once())->method('subFreezeBalance')->with($total);

        $this->freezeService->unfreezeByApplication($application);
    }

    public static function provideUnfreezeBuyCases(): array
    {
        return [
            'unfreeze 500.0' => [500.0],
            'unfreeze 750.5' => [750.5],
        ];
    }
}
