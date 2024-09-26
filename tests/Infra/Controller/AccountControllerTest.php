<?php declare(strict_types=1);

namespace Tests\Infra\Controller;

use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\TestCase;

class AccountControllerTest extends TestCase {
    use AccountUtils;

	public function testCreateAccount(): void {
        $account_id = Uuid::uuid4();
        $response = $this->postJson(
            '/api/v1/account',
            [
                'account_id' => $account_id,
                'currency' => 1,
            ]
        );
        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                'id' => $account_id,
                'sequence' => 0,
                'currency' => 1,
                'debit_amount' => 0,
                'credit_amount' => 0,
                'balance' => 0,
                'datetime' => $this->getNow()->toIso8601String(),
            ]
        );
    }

    public function testCreateAnExistentAccount(): void {
        $account_id = $this->createAccount();
        $response = $this->postJson(
            '/api/v1/account',
            [
                'account_id' => $account_id,
                'currency' => 1,
            ]
        );
        self::assertEquals(409, $response->getStatusCode());
        $response->assertExactJson([
            'error' => "Account with id $account_id already exists"
        ]);
    }

    public function testGetAccountWithSequence(): void {
        $account_id = $this->createAccount();
        $response = $this->get("/api/v1/account/$account_id/0");
        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                'id' => $account_id,
                'sequence' => 0,
                'currency' => 1,
                'debit_amount' => 0,
                'credit_amount' => 0,
                'balance' => 0,
                'datetime' => $this->getNow()->toIso8601String(),
            ]
        );
    }

    public function testGetAccountWithSequenceNotFound(): void {
        $account_id = $this->createAccount();
        $response = $this->get("/api/v1/account/$account_id/1");
        self::assertEquals(404, $response->getStatusCode());
        $response->assertExactJson([
            'error' => "Account not found: $account_id"
        ]);
    }

    public function testListAccount(): void {
        $account_id = $this->createAccount();
        $this->creditAmountToAccount($account_id, 100);
        $this->creditAmountToAccount($account_id, 200);
        $this->creditAmountToAccount($account_id, 300);

        $response = $this->get("/api/v1/account/$account_id?limit=2&beforeSequence=2");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson([
            [
                'id' => $account_id,
                'sequence' => 1,
                'currency' => 1,
                'debit_amount' => 0,
                'credit_amount' => 100,
                'balance' => 100,
                'datetime' => $this->getNow()->toIso8601String(),
            ],
            [
                'id' => $account_id,
                'sequence' => 0,
                'currency' => 1,
                'debit_amount' => 0,
                'credit_amount' => 0,
                'balance' => 0,
                'datetime' => $this->getNow()->toIso8601String(),
            ],
        ]);
    }

    public function testListAccountInvalidLimit(): void {
        $account_id = $this->createAccount();
        $response = $this->get("/api/v1/account/$account_id?limit=101");
        self::assertEquals(400, $response->getStatusCode());
        $response->assertExactJson([
            'error' => 'Limit must be between 1 and 100'
        ]);
    }
}
