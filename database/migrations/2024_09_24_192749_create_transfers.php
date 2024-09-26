<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = <<<SQL
        CREATE TABLE transfers(
            id UUID NOT NULL PRIMARY KEY,
            debit_account_id UUID NOT NULL,
            debit_sequence INT NOT NULL,
            credit_account_id UUID NOT NULL,
            credit_sequence INT NOT NULL,
            currency INT NOT NULL,
            amount BIGINT NOT NULL,
            metadata JSON NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (debit_account_id, debit_sequence) REFERENCES accounts(id, sequence),
            FOREIGN KEY (credit_account_id, credit_sequence) REFERENCES accounts(id, sequence)
        );
SQL;

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
