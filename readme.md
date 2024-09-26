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
"sequence": 0,
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
      "sequence": 1,
      "currency": 1,
      "debit_amount": 100,
      "credit_amount": 0,
      "balance": -100,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "sequence": 5,
      "currency": 1,
      "debit_amount": 0,
      "credit_amount": 900,
      "balance": 900,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922614-d5fe-7e15-831e-aa4a351ce9fd",
      "sequence": 5,
      "currency": 1,
      "debit_amount": 1100,
      "credit_amount": 0,
      "balance": -1100,
      "datetime": "2024-09-26T16:34:11+00:00"
    },
    {
      "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "sequence": 6,
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
      "debit_sequence": 1,
      "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "credit_sequence": 5,
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
      "debit_sequence": 5,
      "credit_account_id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
      "credit_sequence": 6,
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

### Get an Account with specific sequence

Request:
```http request
GET /api/v1/account/01922615-20de-7dc6-a3da-1682e3c0ecac/6
```

Response:

```
HTTP/1.1 200 OK

{
  "id": "01922615-20de-7dc6-a3da-1682e3c0ecac",
  "sequence": 6,
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
GET /api/v1/account/01922a11-030f-7f12-97ff-e54213dc1da7?limit=2&beforeSequence=200
```

Response: 
```
[
  {
    "id": "01922a11-030f-7f12-97ff-e54213dc1da7",
    "sequence": 199,
    "currency": 1,
    "debit_amount": 53409,
    "credit_amount": 47179,
    "balance": -6230,
    "datetime": "2024-09-25T16:54:39+00:00"
  },
  {
    "id": "01922a11-030f-7f12-97ff-e54213dc1da7",
    "sequence": 198,
    "currency": 1,
    "debit_amount": 53361,
    "credit_amount": 47179,
    "balance": -6182,
    "datetime": "2024-09-25T16:54:38+00:00"
  }
]
```
