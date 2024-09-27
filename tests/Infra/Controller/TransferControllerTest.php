<?php declare(strict_types=1);

namespace Tests\Infra\Controller;

use App\Domain\Money;
use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\Support\TransferUtils;
use Tests\TestCase;

class TransferControllerTest extends TestCase {
    use AccountUtils;
    use TransferUtils;

    public function testExecuteTransfer(): void {
        $account_1_id = $this->createAccount();
        $account_2_id = $this->createAccount();
        $transfer_1_id = Uuid::uuid4();
        $transfer_2_id = Uuid::uuid4();

        $response = $this->postJson(
            '/api/v1/transfer',
            [
                [
                    'transfer_id' => $transfer_1_id,
                    'debit_account_id' => $account_1_id,
                    'credit_account_id' => $account_2_id,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => (object)['description' => 'A description']
                ],
                [
                    'transfer_id' => $transfer_2_id,
                    'debit_account_id' => $account_1_id,
                    'credit_account_id' => $account_2_id,
                    'currency' => 1,
                    'amount' => 300,
                    'metadata' => (object)['description' => 'A description']
                ],
            ]
        );

        self::assertEquals(201, $response->getStatusCode());
        $response->assertExactJson(
            [
                'accounts' => [
                    [
                        'id' => $account_1_id,
                        'version' => 1,
                        'currency' => 1,
                        'debit_amount' => 100,
                        'credit_amount' => 0,
                        'balance' => -100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_2_id,
                        'version' => 1,
                        'currency' => 1,
                        'debit_amount' => 0,
                        'credit_amount' => 100,
                        'balance' => 100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_1_id,
                        'version' => 2,
                        'currency' => 1,
                        'debit_amount' => 400,
                        'credit_amount' => 0,
                        'balance' => -400,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_2_id,
                        'version' => 2,
                        'currency' => 1,
                        'debit_amount' => 0,
                        'credit_amount' => 400,
                        'balance' => 400,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],

                ],
                'transfers' => [
                    [
                        'id' => $transfer_1_id,
                        'debit_account_id' => $account_1_id,
                        'debit_version' => 1,
                        'credit_account_id' => $account_2_id,
                        'credit_version' => 1,
                        'currency' => 1,
                        'amount' => 100,
                        'metadata' => (object)['description' => 'A description'],
                        'created_at' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $transfer_2_id,
                        'debit_account_id' => $account_1_id,
                        'debit_version' => 2,
                        'credit_account_id' => $account_2_id,
                        'credit_version' => 2,
                        'currency' => 1,
                        'amount' => 300,
                        'metadata' => (object)['description' => 'A description'],
                        'created_at' => $this->getNow()->toIso8601String(),
                    ],
                ],
            ]
        );
    }

    public function testTransferToSameAccount(): void {
        $account_1_id = $this->createAccount();
        $response = $this->postJson(
            '/api/v1/transfer',
            [
                [
                    'transfer_id' => Uuid::uuid4(),
                    'debit_account_id' => $account_1_id,
                    'credit_account_id' => $account_1_id,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => (object)['description' => 'A description']
                ],
            ]
        );


        self::assertEquals(422, $response->getStatusCode());
        $response->assertExactJson(
            [
                'error' => "Debit and credit account are the same. $account_1_id"
            ]
        );
    }

    public function testGetTransfer(): void {
        $account_1_id = $this->createAccount();
        $account_2_id = $this->createAccount();
        $transfer_id = $this->createTransfer($account_1_id, $account_2_id);

        $response = $this->get("/api/v1/transfer/$transfer_id");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
                [
                    'id' => $transfer_id,
                    'debit_account_id' => $account_1_id,
                    'debit_version' => 1,
                    'credit_account_id' => $account_2_id,
                    'credit_version' => 1,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => [],
                    'created_at' => $this->getNow()->toIso8601String(),
                ]
            );
    }

    public function testGetTransferNotFound(): void {
        $transfer_id = Uuid::uuid4();

        $response = $this->get("/api/v1/transfer/$transfer_id");

        self::assertEquals(404, $response->getStatusCode());
        $response->assertExactJson(
            [
                'error' => "Transfer not found: $transfer_id"
            ]
        );
    }

    public function testListTransferFromCreditAccount(): void {
        $debit_account_id_1 = $this->createAccount();
        $debit_account_id_2 = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id_1, $credit_account_id, new Money(100, 1));
        $transfer_id_2 = $this->createTransfer($debit_account_id_2, $credit_account_id, new Money(150, 1));
        $this->createTransfer($debit_account_id_1, $credit_account_id, new Money(100, 1));

        $response = $this->get("/api/v1/transfer/credit/$credit_account_id?limit=2&beforeVersion=3");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                [
                    'id' => $transfer_id_2,
                    'debit_account_id' => $debit_account_id_2,
                    'debit_version' => 1,
                    'credit_account_id' => $credit_account_id,
                    'credit_version' => 2,
                    'currency' => 1,
                    'amount' => 150,
                    'metadata' => [],
                    'created_at' => $this->getNow()->toIso8601String(),
                ],
                [
                    'id' => $transfer_id_1,
                    'debit_account_id' => $debit_account_id_1,
                    'debit_version' => 1,
                    'credit_account_id' => $credit_account_id,
                    'credit_version' => 1,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => [],
                    'created_at' => $this->getNow()->toIso8601String(),
                ],
            ]
        );
    }

    public function testListTransferFromDebitAccount(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id_1 = $this->createAccount();
        $credit_account_id_2 = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id, $credit_account_id_1, new Money(100, 1));
        $transfer_id_2 = $this->createTransfer($debit_account_id, $credit_account_id_2, new Money(150, 1));
        $this->createTransfer($debit_account_id, $credit_account_id_1, new Money(100, 1));

        $response = $this->get("/api/v1/transfer/debit/$debit_account_id?limit=2&beforeVersion=3");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                [
                    'id' => $transfer_id_2,
                    'debit_account_id' => $debit_account_id,
                    'debit_version' => 2,
                    'credit_account_id' => $credit_account_id_2,
                    'credit_version' => 1,
                    'currency' => 1,
                    'amount' => 150,
                    'metadata' => [],
                    'created_at' => $this->getNow()->toIso8601String(),
                ],
                [
                    'id' => $transfer_id_1,
                    'debit_account_id' => $debit_account_id,
                    'debit_version' => 1,
                    'credit_account_id' => $credit_account_id_1,
                    'credit_version' => 1,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => [],
                    'created_at' => $this->getNow()->toIso8601String(),
                ],
            ]
        );
    }

    public function testGetTransferFromCreditAccountAndVersion(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id = $this->createTransfer($debit_account_id, $credit_account_id);

        $response = $this->get("/api/v1/transfer/credit/$credit_account_id/1");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                'id' => $transfer_id,
                'debit_account_id' => $debit_account_id,
                'debit_version' => 1,
                'credit_account_id' => $credit_account_id,
                'credit_version' => 1,
                'currency' => 1,
                'amount' => 100,
                'metadata' => [],
                'created_at' => $this->getNow()->toIso8601String(),
            ]
        );
    }

    public function testGetTransferFromDebitAccountAndVersion(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id = $this->createTransfer($debit_account_id, $credit_account_id);

        $response = $this->get("/api/v1/transfer/debit/$debit_account_id/1");

        self::assertEquals(200, $response->getStatusCode());
        $response->assertExactJson(
            [
                'id' => $transfer_id,
                'debit_account_id' => $debit_account_id,
                'debit_version' => 1,
                'credit_account_id' => $credit_account_id,
                'credit_version' => 1,
                'currency' => 1,
                'amount' => 100,
                'metadata' => [],
                'created_at' => $this->getNow()->toIso8601String(),
            ]
        );
    }

    public function testExecuteTransferWithConditionalPassing(): void {
        $account_1_id = $this->createAccount();
        $account_2_id = $this->createAccount();
        $transfer_id = Uuid::uuid4();

        $response = $this->postJson(
            '/api/v1/transfer',
            [
                [
                    'transfer_id' => $transfer_id,
                    'debit_account_id' => $account_1_id,
                    'credit_account_id' => $account_2_id,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => (object)['description' => 'A description'],
                    'conditionals' => [
                        [
                            'type' => 'debit_account_balance_greater_than_or_equal_to',
                            'value' => -100,
                        ],
                    ],
                ],
            ]
        );

        self::assertEquals(201, $response->getStatusCode());
        $response->assertExactJson(
            [
                'accounts' => [
                    [
                        'id' => $account_1_id,
                        'version' => 1,
                        'currency' => 1,
                        'debit_amount' => 100,
                        'credit_amount' => 0,
                        'balance' => -100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_2_id,
                        'version' => 1,
                        'currency' => 1,
                        'debit_amount' => 0,
                        'credit_amount' => 100,
                        'balance' => 100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                ],
                'transfers' => [
                    [
                        'id' => $transfer_id,
                        'debit_account_id' => $account_1_id,
                        'debit_version' => 1,
                        'credit_account_id' => $account_2_id,
                        'credit_version' => 1,
                        'currency' => 1,
                        'amount' => 100,
                        'metadata' => (object)['description' => 'A description'],
                        'created_at' => $this->getNow()->toIso8601String(),
                    ],
                ],
            ]
        );
    }

    public function testExecuteTransferWithConditionalFailing(): void {
        $account_1_id = $this->createAccount();
        $account_2_id = $this->createAccount();
        $transfer_id = Uuid::uuid4();

        $response = $this->postJson(
            '/api/v1/transfer',
            [
                [
                    'transfer_id' => $transfer_id,
                    'debit_account_id' => $account_1_id,
                    'credit_account_id' => $account_2_id,
                    'currency' => 1,
                    'amount' => 100,
                    'metadata' => (object)['description' => 'A description'],
                    'conditionals' => [
                        [
                            'type' => 'debit_account_balance_greater_than_or_equal_to',
                            'value' => 0,
                        ],
                    ],
                ],
            ]
        );

        $response->assertExactJson(
            [
                'error' => "Failed executing transfer {$transfer_id}. Debit account balance would be less than 0"
            ]
        );
    }
}
