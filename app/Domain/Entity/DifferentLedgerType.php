<?php declare(strict_types=1);

namespace App\Domain\Entity;

class DifferentLedgerType extends \Exception {

	public function __construct(int $ledger_type_a, int $ledger_type_b) {
        parent::__construct(
            "Different ledger type: {$ledger_type_a} and {$ledger_type_b}"
        );
    }
}
