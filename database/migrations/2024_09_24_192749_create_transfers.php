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
            debit_version INT NOT NULL,
            credit_account_id UUID NOT NULL,
            credit_version INT NOT NULL,
            ledger_type INT NOT NULL,
            amount BIGINT NOT NULL,
            metadata JSON NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (debit_account_id, debit_version) REFERENCES accounts(id, version),
            FOREIGN KEY (credit_account_id, credit_version) REFERENCES accounts(id, version)
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
