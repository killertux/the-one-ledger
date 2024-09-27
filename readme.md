# The One Ledger

> One Ledger to rule them All

## Start the Application.

Just copy the file `.env.example` to `.env`. Than start the container with `docker-compose up -d`.

The application will be listening for request HTTP requests at the port `8001`. You can see status of CockroachDB at the port `8080`.


## Examples

### Create Account

Request:
```http request
POST /api/v1/account
Content-Type: application/json

{
  "account_id": "01922614-d5fe-7e15-831e-aa4a351ce9fd",
  "currency": 1
}
```

Response:

```
HTTP/1.1 200 OK

{
"id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
"version": 0,
"currency": 1,
"debit_amount": 0,
"credit_amount": 0,
"balance": 0,
"datetime": "2024-09-26T16:33:06+00:00"
}
```

### Execute Transfers

Request:

```http request
POST /api/v1/transfer
Content-Type: application/json

[
  {
    "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
    "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
    "transfer_id": "01922716-0ac8-7cf6-99e1-be722feb08ad",
    "currency": 1,
    "amount": 100,
    "metadata": {
        "description": "Transfer from account 1 to account 2",
        "payment_id": 123456
    }
  },
  {
    "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9fd",
    "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
    "transfer_id": "01923616-0ac8-7cf6-99e0-be722feb09ae",
    "currency": 1,
    "amount": 300,
    "metadata": {
      "description": "Transfer from account 2 to account 1",
      "payment_id": 233123
    }
  }
]
```

Response:
```
HTTP/1.1 201 Created

{
  "accounts": [
    {
      "id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
      "version": 1,
      "currency": 1,
      "debit_amount": 100,
      "credit_amount": 0,
      "balance": -100,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "version": 5,
      "currency": 1,
      "debit_amount": 0,
      "credit_amount": 900,
      "balance": 900,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922614-d5fe-7e15-831e-aa4a351ce9fd",
      "version": 5,
      "currency": 1,
      "debit_amount": 1100,
      "credit_amount": 0,
      "balance": -1100,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "version": 6,
      "currency": 1,
      "debit_amount": 0,
      "credit_amount": 1200,
      "balance": 1200,
      "datetime": "2024-09-26T16:34:11+00:00"
    }
  ],
  "transfers": [
    {
      "id": "01922716-0ac8-7cf6-99e1-be722feb08ad",
      "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
      "debit_version": 1,
      "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "credit_version": 5,
      "currency": 1,
      "amount": 100,
      "metadata": {
        "description": "Transfer from account 1 to account 2",
        "payment_id": 123456
      },
      "created_at": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01923616-0ac8-7cf6-99e0-be722feb09ae",
      "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9fd",
      "debit_version": 5,
      "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "credit_version": 6,
      "currency": 1,
      "amount": 300,
      "metadata": {
        "description": "Transfer from account 2 to account 1",
        "payment_id": 233123
      },
      "created_at": "2024-09-26T16:34:11+00:00"
    }
  ]
}
```

### Execute transfer with conditional 

Request:
```http request
POST /api/v1/transfer
Content-Type: application/json

[
  {
    "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9fe",
    "credit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
    "transfer_id": "01922716-0ac8-7cf6-99e1-be722feb08bd",
    "currency": 1,
    "amount": 100,
    "metadata": {},
    "conditionals": [
      {
        "type": "debit_account_balance_greater_or_equal_than",
        "value": 0
      }
    ]
  }
]
```

Successful Response:
```
HTTP/1.1 201 Created

{
  "accounts": [
    {
      "id": "01922614-d5fe-7e15-831e-aa4a351ce9fe",
      "version": 2,
      "currency": 1,
      "debit_amount": 100,
      "credit_amount": 100,
      "balance": 0,
      "datetime": "2024-09-27T16:47:38+00:00"
    },
    {
      "id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
      "version": 1,
      "currency": 1,
      "debit_amount": 0,
      "credit_amount": 100,
      "balance": 100,
      "datetime": "2024-09-27T16:47:38+00:00"
    }
  ],
  "transfers": [
    {
      "id": "01922716-0ac8-7cf6-99e1-be722feb08bd",
      "debit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9fe",
      "debit_version": 2,
      "credit_account_id": "01922614-d5fe-7e15-831e-aa4a351ce9ff",
      "credit_version": 1,
      "currency": 1,
      "amount": 100,
      "metadata": {},
      "created_at": "2024-09-27T16:47:38+00:00"
    }
  ]
}
```

Failed response:
```
HTTP/1.1 409 Conflict

{
  "error": "Failed executing transfer 01922716-0ac8-7cf6-99e1-be722feb07bf. Debit account balance would be less than 0"
}
```


### Get an Account with specific version

Request:
```http request
GET /api/v1/account/01922615-20de-7dc6-a3da-1682e3c0ecac/6
```

Response:

```
HTTP/1.1 200 OK

{
  "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
  "version": 6,
  "currency": 1,
  "debit_amount": 0,
  "credit_amount": 1200,
  "balance": 1200,
  "datetime": "2024-09-26T16:34:11+00:00"
}
```

### List Account History

Request
```http request
GET /api/v1/account/01922a11-030f-7f12-97ff-e54213dc1da7?limit=2&beforeversion=200
```

Response: 
```
[
  {
    "id": "01922a11-030f-7f12-97ff-e54213dc1da7",
    "version": 199,
    "currency": 1,
    "debit_amount": 53409,
    "credit_amount": 47179,
    "balance": -6230,
    "datetime": "2024-09-25T16:54:39+00:00"
  },
  {
    "id": "01922a11-030f-7f12-97ff-e54213dc1da7",
    "version": 198,
    "currency": 1,
    "debit_amount": 53361,
    "credit_amount": 47179,
    "balance": -6182,
    "datetime": "2024-09-25T16:54:38+00:00"
  }
]
```

### Get Transfer by ID

Request:
```http request
GET /api/v1/transfer/01922fda-bec1-70a1-b7b4-ed4d46331c14
```

Response:
```
HTTP/1.1 200 OK

{
  "id": "01922fda-bec1-70a1-b7b4-ed4d46331c14",
  "debit_account_id": "01922fda-bd70-79a3-a5ee-fa7e701bec79",
  "debit_version": 1,
  "credit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
  "credit_version": 1,
  "currency": 1,
  "amount": 161,
  "metadata": {
    "description": "Transfer from account 01922fda-bd70-79a3-a5ee-fa7e701bec79 to account 01922fda-be12-71d2-8b61-d56471845458"
  },
  "created_at": "2024-09-26T19:41:58+00:00"
}
```

### List credit transfers from account

Request:
```http request
GET /api/v1/transfer/credit/01922fda-be12-71d2-8b61-d56471845458?limit=2&beforeVersion=20
```

Response:
```
HTTP/1.1 200 OK

[
  {
    "id": "01922fda-cf57-7673-aa45-d1c9512fbeea",
    "debit_account_id": "01922fda-bbd8-73e2-a23f-18312b807ce5",
    "debit_version": 22,
    "credit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
    "credit_version": 19,
    "currency": 1,
    "amount": 166,
    "metadata": {
      "description": "Transfer from account 01922fda-bbd8-73e2-a23f-18312b807ce5 to account 01922fda-be12-71d2-8b61-d56471845458"
    },
    "created_at": "2024-09-26T19:42:03+00:00"
  },
  {
    "id": "01922fda-cec0-78d0-8323-9ae8b8cd3b78",
    "debit_account_id": "01922fda-bbd8-73e2-a23f-18312b807ce5",
    "debit_version": 19,
    "credit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
    "credit_version": 18,
    "currency": 1,
    "amount": 263,
    "metadata": {
      "description": "Transfer from account 01922fda-bbd8-73e2-a23f-18312b807ce5 to account 01922fda-be12-71d2-8b61-d56471845458"
    },
    "created_at": "2024-09-26T19:42:02+00:00"
  }
]
```

### List debit transfers from account

Request:
```http request
GET /api/v1/transfer/debit/01922fda-be12-71d2-8b61-d56471845458?limit=2&beforeVersion=16
```

Response:
```
HTTP/1.1 200 OK

[
  {
    "id": "01922fda-cbb1-70e3-a25d-03f7d1ad43dc",
    "debit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
    "debit_version": 15,
    "credit_account_id": "01922fda-b9a5-7d81-a629-c50263454b4b",
    "credit_version": 23,
    "currency": 1,
    "amount": 242,
    "metadata": {
      "description": "Transfer from account 01922fda-be12-71d2-8b61-d56471845458 to account 01922fda-b9a5-7d81-a629-c50263454b4b"
    },
    "created_at": "2024-09-26T19:42:02+00:00"
  },
  {
    "id": "01922fda-c86e-7773-8264-0c33686bd755",
    "debit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
    "debit_version": 12,
    "credit_account_id": "01922fda-b9a5-7d81-a629-c50263454b4b",
    "credit_version": 21,
    "currency": 1,
    "amount": 584,
    "metadata": {
      "description": "Transfer from account 01922fda-be12-71d2-8b61-d56471845458 to account 01922fda-b9a5-7d81-a629-c50263454b4b"
    },
    "created_at": "2024-09-26T19:42:01+00:00"
  }
]
```

### Get credit transfer to account

Request:
```http request
GET /api/v1/transfer/credit/01922fda-be12-71d2-8b61-d56471845458/19
```

Response:
```
HTTP/1.1 200 OK

{
  "id": "01922fda-cf57-7673-aa45-d1c9512fbeea",
  "debit_account_id": "01922fda-bbd8-73e2-a23f-18312b807ce5",
  "debit_version": 22,
  "credit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
  "credit_version": 19,
  "currency": 1,
  "amount": 166,
  "metadata": {
    "description": "Transfer from account 01922fda-bbd8-73e2-a23f-18312b807ce5 to account 01922fda-be12-71d2-8b61-d56471845458"
  },
  "created_at": "2024-09-26T19:42:03+00:00"
}
```

### Get debit transfer to account

Request:
```http request
GET /api/v1/transfer/debit/01922fda-be12-71d2-8b61-d56471845458/15
```

Response:
```
HTTP/1.1 200 OK

{
  "id": "01922fda-cbb1-70e3-a25d-03f7d1ad43dc",
  "debit_account_id": "01922fda-be12-71d2-8b61-d56471845458",
  "debit_version": 15,
  "credit_account_id": "01922fda-b9a5-7d81-a629-c50263454b4b",
  "credit_version": 23,
  "currency": 1,
  "amount": 242,
  "metadata": {
    "description": "Transfer from account 01922fda-be12-71d2-8b61-d56471845458 to account 01922fda-b9a5-7d81-a629-c50263454b4b"
  },
  "created_at": "2024-09-26T19:42:02+00:00"
}
```
