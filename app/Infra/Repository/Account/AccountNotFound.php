<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use Ramsey\Uuid\UuidInterface;

class AccountNotFound extends \Exception {

    public function __construct(UuidInterface $id) {
        parent::__construct(
            "Account not found: {$id}"
        );
    }
}
