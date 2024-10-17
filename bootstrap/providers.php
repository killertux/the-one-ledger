<?php

return array_merge(
    [App\Providers\DynamoDbProvider::class, App\Providers\SleeperProvider::class,],
    env('PERSISTENT_DRIVER') == 'crdb' ? [App\Providers\CrdbAccountRepositoryProvider::class, App\Providers\CrdbTransferRepositoryProvider::class, App\Providers\CrdbTransactionProvider::class] : [],
    env('PERSISTENT_DRIVER') == 'dynamodb' ? [App\Providers\DynamoDbAccountRepositoryProvider::class, App\Providers\DynamoDbTransferRepositoryProvider::class, App\Providers\DynamoDbTransactionProvider::class] : []
);
