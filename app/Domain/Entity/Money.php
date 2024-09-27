<?php declare(strict_types=1);

namespace App\Domain\Entity;

readonly class Money {

    public function __construct(
        private int $amount,
        private int $currency,
    ) {}

    /**
     * @throws DifferentCurrency
     */
    public function add(Money $money): self {
        if ($this->currency !== $money->currency) {
            throw new DifferentCurrency($this->currency, $money->currency);
        }
        return new self(
            $this->amount + $money->amount,
            $this->currency
        );
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getCurrency(): int {
        return $this->currency;
    }
}
