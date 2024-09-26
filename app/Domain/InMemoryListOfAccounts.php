<?php declare(strict_types=1);

namespace App\Domain;

use App\Infra\Repository\Account\AccountRepository;
use Ramsey\Uuid\UuidInterface;

class InMemoryListOfAccounts extends \ArrayObject {

    private AccountRepository $account_repository;

    public function __construct(AccountRepository $account_repository) {
        $this->account_repository = $account_repository;
        parent::__construct();
    }

    public function offsetGet(mixed $key): Account {
        $string_key = (string) $key;
        if (!$this->offsetExists($string_key)) {
            $this->offsetSet($string_key, $this->account_repository->getAccount($key));
        }
        return parent::offsetGet($string_key);
    }

    public function offsetSet(mixed $key, mixed $value): void {
        $string_key = (string) $key;
        parent::offsetSet($string_key, $value);
    }

}
