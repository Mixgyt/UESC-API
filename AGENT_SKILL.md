# UESC-API Agent Skill

Use this guide when another agent needs to work with the UESC-API HTTP API. This document is fully self-sufficient and contains the complete specification of the HTTP contract, parameters, headers, and expected responses.

## Purpose

This API exposes Bitcoin regtest information in read-only form, with endpoints for general node stats, blocks, transactions, and mempool entries. The only state-mutating actions are authentication, token revocation, and block mining.

## General API Rules

- **Content-Type**: All request bodies must be JSON, sent with the header `Content-Type: application/json`.
- **Response Format**: Responses are always JSON. Standard responses wrap the payload in a `"data"` key. Paginated responses include `"links"` and `"meta"` keys.
- **Satoshis**: All currency amounts (fees, transaction outputs, miner rewards, block fees) are represented in **satoshis** (integers), never as fractional BTC.
- **Raw fields**: The database stores raw JSON payloads returned by Bitcoin Core in a `raw` field. However, `raw` fields are **never** returned by the API responses.
- **Base URL**: The default local server runs at `http://127.0.0.1:8000`.

---

## Authentication & Authorization

Protected endpoints require a Sanctum token sent in the headers:
`Authorization: Bearer {token}`

### 1. POST /api/auth/token
- **Description**: Authenticates a user and issues a Sanctum plain-text token.
- **Headers**: `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "email": "admin@regtest.local",
    "password": "secret"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "token": "1|examplePlainTextToken"
  }
  ```
- **Response (422 Unprocessable Content)**: Validation errors (e.g. invalid credentials or missing fields).

### 2. DELETE /api/auth/token
- **Description**: Revokes the plain-text token currently authenticated.
- **Headers**: `Authorization: Bearer {token}`
- **Response (204 No Content)**: Empty body.
- **Response (401 Unauthorized)**: Invalid or missing token.

---

## Node Information

### 3. GET /api/node/info
- **Description**: Retrieves current status of the Bitcoin Core node and network.
- **Headers**: None (Public)
- **Response (200 OK)**:
  ```json
  {
    "data": {
      "chain": "regtest",
      "blocks": 142,
      "headers": 142,
      "difficulty": 4.656542373906925e-10,
      "network_active": true,
      "version": 250000
    }
  }
  ```

---

## Mining (Testing Utilities)

### 4. POST /api/node/mine
- **Description**: Mines exactly 1 block in regtest sending the reward to the provided address. Imposes a global lock (cooldown) regulated by `MINING_COOLDOWN_SECONDS` in `.env`.
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "address": "bcrt1qexampleaddress"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "message": "Blocks mined successfully.",
    "block_hashes": [
      "00000000aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
    ]
  }
  ```
- **Response (429 Too Many Requests)**: Returned if cooldown lock is active.
  ```json
  {
    "message": "Mining is on cooldown.",
    "remaining_seconds": 115
  }
  ```

---

## Blocks

### 5. GET /api/blocks
- **Description**: Lists paginated blocks, sorted by height in descending order.
- **Headers**: None (Public)
- **Query Parameters**:
  - `page` (optional, integer): The page number to retrieve. Default: 1.
  - `per_page` (optional, integer): Number of records per page (capped at 100).
- **Response (200 OK)**:
  ```json
  {
    "data": [
      {
        "hash": "00000000aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
        "height": 142,
        "time": "2026-06-12T20:10:00+00:00",
        "tx_count": 2,
        "size": 905,
        "weight": 3620,
        "difficulty": 4.656542373906925e-10,
        "miner_reward_sat": 5000000000,
        "transactions": [
          "txid1",
          "txid2"
        ]
      }
    ],
    "links": {
      "first": "http://127.0.0.1:8000/api/blocks?page=1",
      "last": "http://127.0.0.1:8000/api/blocks?page=8",
      "prev": null,
      "next": "http://127.0.0.1:8000/api/blocks?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 8,
      "path": "http://127.0.0.1:8000/api/blocks",
      "per_page": 20,
      "to": 20,
      "total": 142
    }
  }
  ```

### 6. GET /api/blocks/latest
- **Description**: Returns the block with the highest height synced in the DB.
- **Headers**: None (Public)
- **Response (200 OK)**: Matches single item format of `/api/blocks`.

### 7. GET /api/blocks/{hashOrHeight}
- **Description**: Retreives a block by its 64-char hex hash or integer height.
- **Headers**: None (Public)
- **Response (200 OK)**: Matches single item format of `/api/blocks`.
- **Response (404 Not Found)**: If no block is found in local DB.

### 8. GET /api/blocks/{hashOrHeight}/transactions
- **Description**: Returns paginated transactions belonging to the specified block.
- **Headers**: None (Public)
- **Response (200 OK)**: Paginated transactions list matching the format of `/api/transactions`.

---

## Transactions

### 9. GET /api/transactions
- **Description**: Lists paginated, confirmed transactions in local DB, sorted by `confirmed_at` descending.
- **Headers**: None (Public)
- **Query Parameters**:
  - `page` (optional, integer): Page number.
  - `per_page` (optional, integer): Capped at 100.
- **Response (200 OK)**:
  ```json
  {
    "data": [
      {
        "txid": "txid1",
        "status": "confirmed",
        "fee_sat": 1000,
        "fee_rate_sat_vbyte": 2.5,
        "size": 400,
        "vsize": 400,
        "input_count": 1,
        "output_count": 2,
        "confirmed_at": "2026-06-12T20:10:00+00:00",
        "block_height": 142,
        "block_hash": "00000000aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
        "inputs": [
          {
            "txid": "prevtxid",
            "vout": 0,
            "value_sat": 5000000000
          }
        ],
        "outputs": [
          {
            "address": "bcrt1qexampleaddress",
            "value_sat": 4999998000
          }
        ]
      }
    ],
    "links": { ... },
    "meta": { ... }
  }
  ```

### 10. GET /api/transactions/{txid}
- **Description**: Returns details for a transaction by ID. If not present in local DB, queries Bitcoin Core RPC dynamically.
- **Headers**: None (Public)
- **Response (200 OK)**: Returns the transaction details wrapper:
  ```json
  {
    "data": {
      "txid": "txid1",
      "status": "confirmed", // or "unconfirmed" if found in RPC mempool
      "fee_sat": 1000,
      ...
    }
  }
  ```

---

## Mempool

### 11. GET /api/mempool
- **Description**: Lists paginated pending mempool transactions.
- **Headers**: None (Public)
- **Query Parameters**:
  - `sort`: `fee_rate` or `time`
  - `order`: `asc` or `desc`
- **Response (200 OK)**:
  ```json
  {
    "data": [
      {
        "txid": "deadbeef00000000000000000000000000000000000000000000000000000000",
        "fee_sat": 8600,
        "vsize": 512,
        "fee_rate_sat_vbyte": 16.796875,
        "depends": [],
        "time": "2026-06-12T20:12:00+00:00"
      }
    ],
    "links": { ... },
    "meta": { ... }
  }
  ```

### 12. GET /api/mempool/summary
- **Description**: Retrieves aggregate mempool stats calculated from local SQL table.
- **Headers**: None (Public)
- **Response (200 OK)**:
  ```json
  {
    "data": {
      "tx_count": 5,
      "total_vsize": 2048,
      "total_size_vbytes": 2048,
      "fee_min_sat_vbyte": 1.0,
      "fee_max_sat_vbyte": 10.0,
      "fee_avg_sat_vbyte": 4.2,
      "total_fees_sat": 8600
    }
  }
  ```

### 13. GET /api/mempool/{txid}
- **Description**: Detail of an entry in the mempool.
- **Headers**: None (Public)
- **Response (200 OK)**: Returns a single mempool entry wrapper.

---

## Practical guidelines for modifications

1. **Keep read endpoints public**: Avoid adding token requirements to read endpoints unless explicitly requested.
2. **Keep write/mutation actions protected**: Ensure `POST` and `DELETE` requests use `auth:sanctum` where applicable.
3. **Preserve the current response shape**: Do not change structural keys like `data`, `links`, or `meta`.
4. **Use database queue worker**: Sinking/syncing with Bitcoin Core happens in background jobs (`SyncBlockchainJob`). Run `php artisan queue:work` during development.
5. **Updating tests**: Any contract modification requires adjusting test assertions in `tests/Feature`. Verify by running `vendor/bin/phpunit`.
