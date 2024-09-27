<?php declare(strict_types=1);

namespace Tests\Domain\Entity;

use App\Domain\Entity\DifferentCurrency;
use App\Domain\Entity\Money;
use Tests\TestCase;

class MoneyTest extends TestCase {

    public function testCreateAndSeeMoney(): void {
        $money = new Money(100, 1);
        $this->assertEquals(100, $money->getAmount());
        $this->assertEquals(1, $money->getCurrency());
    }

    public function testAddMoneySameCurrency(): void {
        $money_a = new Money(100, 1);
        $money_b = new Money(200, 1);
        $money_c = $money_a->add($money_b);
        self::assertEquals(300, $money_c->getAmount());
        self::assertEquals(1, $money_c->getCurrency());
    }

    public function testAddMoneyDifferentCurrency_ShouldThrowException(): void {
        $this->expectException(DifferentCurrency::class);
        $this->expectExceptionMessage('Different currency: 1 and 2');
        $money_a = new Money(100, 1);
        $money_b = new Money(200, 2);
        $money_a->add($money_b);
    }
}
