<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use Ramsey\Uuid\UuidInterface;

class AccountAlreadyExists extends \Exception {

	public function __construct(UuidInterface $account_id) {
        parent::__construct("Account with id $account_id already exists");
    }
}
