<?php declare(strict_types=1);

namespace Tests\Infra\Controller;

use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\TestCase;

class TransferControllerTest extends TestCase {
    use AccountUtils;

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
                        'sequence' => 1,
                        'currency' => 1,
                        'debit_amount' => 100,
                        'credit_amount' => 0,
                        'balance' => -100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_2_id,
                        'sequence' => 1,
                        'currency' => 1,
                        'debit_amount' => 0,
                        'credit_amount' => 100,
                        'balance' => 100,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_1_id,
                        'sequence' => 2,
                        'currency' => 1,
                        'debit_amount' => 400,
                        'credit_amount' => 0,
                        'balance' => -400,
                        'datetime' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $account_2_id,
                        'sequence' => 2,
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
                        'debit_sequence' => 1,
                        'credit_account_id' => $account_2_id,
                        'credit_sequence' => 1,
                        'currency' => 1,
                        'amount' => 100,
                        'metadata' => (object)['description' => 'A description'],
                        'created_at' => $this->getNow()->toIso8601String(),
                    ],
                    [
                        'id' => $transfer_2_id,
                        'debit_account_id' => $account_1_id,
                        'debit_sequence' => 2,
                        'credit_account_id' => $account_2_id,
                        'credit_sequence' => 2,
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
}
