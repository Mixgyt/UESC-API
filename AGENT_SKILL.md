# UESC-API Agent Skill

Use this guide when another agent needs to work with the UESC-API HTTP API.

## Purpose

This API exposes Bitcoin regtest information in read-only form.
Only token revocation is a protected action.

## What the API does

- Returns node status information.
- Returns blocks and the latest mined block.
- Returns confirmed transactions.
- Returns mempool entries and mempool summary data.
- Issues and revokes Sanctum tokens.

## What is public

These endpoints do not require a token:

- `GET /api/node/info`
- `GET /api/blocks`
- `GET /api/blocks/latest`
- `GET /api/blocks/{hashOrHeight}`
- `GET /api/blocks/{hashOrHeight}/transactions`
- `GET /api/transactions`
- `GET /api/transactions/{txid}`
- `GET /api/mempool`
- `GET /api/mempool/summary`
- `GET /api/mempool/{txid}`
- `POST /api/auth/token`

## What is protected

This endpoint requires `Authorization: Bearer {token}`:

- `DELETE /api/auth/token`

## How to discover the contract

1. Read [DOCS.md](./DOCS.md) for the full human-facing usage guide.
2. Run `php artisan route:list` to verify the available endpoints and middleware.
3. Run `php artisan test` to confirm the current behavior.

## How to call the API

Use plain HTTP requests or `curl`.

Example pattern:

```bash
curl http://127.0.0.1:8000/api/node/info
```

For the protected revocation endpoint:

```bash
curl -X DELETE http://127.0.0.1:8000/api/auth/token \
  -H "Authorization: Bearer {token}"
```

## Response rules

- Responses are JSON.
- Numeric money values are in satoshis.
- `raw` is never returned directly.
- If a route is public, do not add token requirements unless explicitly asked.

## Endpoint order to learn

1. Start with `GET /api/node/info`.
2. Then inspect `GET /api/blocks/latest`.
3. Then inspect `GET /api/blocks`, `GET /api/transactions`, and `GET /api/mempool`.
4. Use `GET /api/blocks/{hashOrHeight}` and `GET /api/transactions/{txid}` for details.
5. Use `GET /api/mempool/summary` when you need aggregate mempool stats.

## Practical rules for future changes

- Keep read endpoints public.
- Keep write or mutation actions protected.
- Preserve the current response shape unless the request says otherwise.
- If you change endpoint behavior, update the docs and tests together.

