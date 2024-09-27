<?php

use Illuminate\Database\Migrations\Migration;
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
        CREATE TABLE accounts(
            id UUID NOT NULL,
            version BIGINT NOT NULL,
            currency INT NOT NULL,
            debit_amount BIGINT NOT NULL,
            credit_amount BIGINT NOT NULL,
            datetime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id, version DESC)
        );
SQL;
        DB::statement($sql);
        DB::statement('CREATE INDEX accounts_datetime_index ON accounts (datetime);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
