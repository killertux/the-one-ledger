<?php declare(strict_types=1);

namespace App\Domain\Entity;

class DifferentCurrency extends \Exception {

	public function __construct(int $currency, int $currency1) {
        parent::__construct(
            "Different currency: {$currency} and {$currency1}"
        );
    }
}
