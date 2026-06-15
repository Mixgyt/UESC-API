# UESC-API - Guia de uso

Este documento explica como instalar, configurar y usar la API sin leer el codigo fuente.
La API expone informacion de un nodo Bitcoin Core en regtest, bloques, transacciones, mempool, autenticacion por token y un proceso de sincronizacion en background.

## 1. Requisitos del sistema

- PHP 8.2 o superior
- Composer 2.x
- MySQL 8.0 o superior
- Bitcoin Core con soporte para regtest
- Extension PHP requeridas: `ext-json`, `ext-curl`, `ext-mbstring`, `ext-pdo_mysql`

## 2. Configuracion del nodo Bitcoin

La aplicacion se conecta al nodo por JSON-RPC usando el archivo `bitcoin.conf`.
Para un entorno regtest local, un bloque de configuracion valido es este:

```ini
regtest=1
server=1
rpcuser=regtest
rpcpassword=regtest
rpcport=18443
rpcallowip=127.0.0.1
```

Ubicacion comun de `bitcoin.conf`:

- Linux: `~/.bitcoin/bitcoin.conf`
- macOS: `~/Library/Application Support/Bitcoin/bitcoin.conf`
- Windows: `%APPDATA%\Bitcoin\bitcoin.conf`

Comandos utiles para iniciar el nodo, crear una wallet y minar los bloques iniciales:

```bash
bitcoind -regtest -daemon
bitcoin-cli -regtest createwallet "miwallet"
bitcoin-cli -regtest getnewaddress
bitcoin-cli -regtest generatetoaddress 101 <direccion>
```

Se minan 101 bloques porque la recompensa coinbase necesita 100 confirmaciones para madurar.
Eso permite gastar los fondos de prueba en regtest.

## 3. Instalacion del proyecto

Pasos desde cero:

```bash
git clone <repositorio>
cd <carpeta-del-repositorio>
composer install
cp .env.example .env
php artisan key:generate
```

Variables del `.env` que debes revisar:

- `APP_NAME`: nombre visible de la aplicacion.
- `APP_ENV`: normalmente `local` en desarrollo y `production` en despliegue.
- `APP_DEBUG`: deja `true` en desarrollo y `false` en produccion.
- `APP_URL`: URL base de la aplicacion.
- `DB_CONNECTION`: debe ser `mysql`.
- `DB_HOST`: host del servidor MySQL.
- `DB_PORT`: puerto MySQL, normalmente `3306`.
- `DB_DATABASE`: base de datos a usar, en este proyecto `uesc_api`.
- `DB_USERNAME`: usuario de MySQL.
- `DB_PASSWORD`: clave de MySQL.
- `SESSION_DRIVER`: debe ser `database`.
- `CACHE_STORE`: debe ser `database`.
- `QUEUE_CONNECTION`: debe ser `database`.
- `BITCOIN_RPC_HOST`: host del nodo Bitcoin.
- `BITCOIN_RPC_PORT`: puerto RPC del nodo, normalmente `18443`.
- `BITCOIN_RPC_USER`: usuario RPC definido en `bitcoin.conf`.
- `BITCOIN_RPC_PASSWORD`: clave RPC definida en `bitcoin.conf`.
- `BITCOIN_RPC_WALLET`: nombre opcional de wallet para llamadas RPC con contexto de wallet.

El archivo `.env.example` ya viene alineado con el proyecto actual, usando `APP_NAME="UESC-API"` y `DB_DATABASE=uesc_api`.

## 4. Configuracion de la base de datos

Crear la base de datos:

```sql
CREATE DATABASE uesc_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ejecutar migraciones y el seeder inicial:

```bash
php artisan migrate
php artisan db:seed --class=ApiUserSeeder
```

El seeder crea un usuario de desarrollo y muestra un token de acceso en consola.
Ese token se usa para probar los endpoints protegidos.

## 5. Ejecucion de los procesos

La aplicacion usa tres procesos en terminales separadas:

Terminal 1, servidor HTTP:

```bash
php artisan serve
```

Terminal 2, worker de cola:

```bash
php artisan queue:work --sleep=3 --tries=3
```

Terminal 3, scheduler:

```bash
php artisan schedule:work
```

El scheduler dispara `SyncBlockchainJob` cada 30 segundos.
El worker procesa la cola y guarda bloques, transacciones y mempool en MySQL.

## 6. Uso de la API

La API responde en JSON y mantiene los montos monetarios en satoshis.
Los endpoints de lectura son publicos y no requieren autenticacion.
La unica operacion protegida por token es la revocacion del token activo en `DELETE /api/auth/token`.
El campo `raw` nunca se expone directamente en las respuestas.

### POST /api/auth/token

Descripcion: autentica al usuario y retorna un token plano de Sanctum.

Request:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/token \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@regtest.local","password":"secret"}'
```

Response:

```json
{
  "token": "1|examplePlainTextToken"
}
```

### DELETE /api/auth/token

Descripcion: revoca el token activo del usuario autenticado.

Request:

```bash
curl -X DELETE http://127.0.0.1:8000/api/auth/token \
  -H "Authorization: Bearer {token}"
```

Response:

```text
204 No Content
```

### GET /api/node/info

Descripcion: retorna informacion general del nodo y de la red.

Request:

```bash
curl http://127.0.0.1:8000/api/node/info
```

Response:

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

### GET /api/blocks

Descripcion: lista paginada de bloques, ordenada por `height` descendente.

Query params:

- `page`: pagina solicitada.
- `per_page`: cantidad de registros por pagina, con limite interno de 100.

Request:

```bash
curl "http://127.0.0.1:8000/api/blocks?page=1&per_page=20"
```

Response:

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

### GET /api/blocks/latest

Descripcion: retorna el bloque mas reciente guardado en la base de datos.

Request:

```bash
curl http://127.0.0.1:8000/api/blocks/latest
```

Response:

```json
{
  "data": {
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
}
```

### GET /api/blocks/{hashOrHeight}

Descripcion: busca un bloque por hash de 64 caracteres o por altura numerica.

Request:

```bash
curl http://127.0.0.1:8000/api/blocks/142
```

Response:

```json
{
  "data": {
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
}
```

### GET /api/blocks/{hashOrHeight}/transactions

Descripcion: retorna las transacciones de un bloque especifico.

Request:

```bash
curl http://127.0.0.1:8000/api/blocks/142/transactions
```

Response:

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
      "inputs": [],
      "outputs": []
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/blocks/142/transactions?page=1",
    "last": "http://127.0.0.1:8000/api/blocks/142/transactions?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://127.0.0.1:8000/api/blocks/142/transactions",
    "per_page": 100,
    "to": 1,
    "total": 1
  }
}
```

### GET /api/transactions

Descripcion: lista paginada de transacciones confirmadas, ordenada por `confirmed_at` descendente.

Query params:

- `page`: pagina solicitada.
- `per_page`: cantidad de registros por pagina, con limite interno de 100.

Request:

```bash
curl "http://127.0.0.1:8000/api/transactions?page=1&per_page=20"
```

Response:

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
  "links": {
    "first": "http://127.0.0.1:8000/api/transactions?page=1",
    "last": "http://127.0.0.1:8000/api/transactions?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://127.0.0.1:8000/api/transactions",
    "per_page": 20,
    "to": 1,
    "total": 1
  }
}
```

### GET /api/transactions/{txid}

Descripcion: retorna el detalle de una transaccion por txid. Si no existe en base, consulta RPC.

Request:

```bash
curl http://127.0.0.1:8000/api/transactions/txid1
```

Response:

```json
{
  "data": {
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
}
```

### GET /api/mempool

Descripcion: lista paginada de transacciones en mempool.
Permite ordenar por `fee_rate` o por `time`.

Query params:

- `sort`: `fee_rate` o `time`.
- `order`: `asc` o `desc`.
- `page`: pagina solicitada.
- `per_page`: cantidad de registros por pagina, con limite interno de 100.

Request:

```bash
curl "http://127.0.0.1:8000/api/mempool?sort=fee_rate&order=desc&page=1"
```

Response:

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
  "links": {
    "first": "http://127.0.0.1:8000/api/mempool?page=1",
    "last": "http://127.0.0.1:8000/api/mempool?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://127.0.0.1:8000/api/mempool",
    "per_page": 20,
    "to": 1,
    "total": 1
  }
}
```

### GET /api/mempool/summary

Descripcion: retorna estadisticas agregadas del mempool calculadas en MySQL.

Request:

```bash
curl http://127.0.0.1:8000/api/mempool/summary
```

Response:

```json
{
  "data": {
    "tx_count": 5,
    "total_vsize": 2048,
    "total_size_vbytes": 2048,
    "fee_min_sat_vbyte": 1,
    "fee_max_sat_vbyte": 10,
    "fee_avg_sat_vbyte": 4.2,
    "total_fees_sat": 8600
  }
}
```

### GET /api/mempool/{txid}

Descripcion: retorna el detalle de una entrada especifica de mempool.

Request:

```bash
curl http://127.0.0.1:8000/api/mempool/deadbeef00000000000000000000000000000000000000000000000000000000
```

Response:

```json
{
  "data": {
    "txid": "deadbeef00000000000000000000000000000000000000000000000000000000",
    "fee_sat": 8600,
    "vsize": 512,
    "fee_rate_sat_vbyte": 16.796875,
    "depends": [],
    "time": "2026-06-12T20:12:00+00:00"
  }
}
```

## 7. Sincronizacion manual

Para forzar una sincronizacion inmediata sin esperar al scheduler:

```bash
php artisan blockchain:sync
```

Es util en estos casos:

- primer arranque
- despues de reiniciar el nodo Bitcoin
- para depurar datos faltantes en la base local

## 8. Solucion de problemas comunes

- Problema: `503 Service Unavailable`
  - Causa: el nodo Bitcoin no esta corriendo o las credenciales RPC en `.env` son incorrectas.
  - Solucion: verifica `bitcoind`, revisa `BITCOIN_RPC_*` y confirma que el puerto RPC sea accesible.

- Problema: los endpoints retornan datos vacios despues de minar
  - Causa: el worker de cola no esta corriendo.
  - Solucion: ejecuta `php artisan queue:work --sleep=3 --tries=3`.

- Problema: `SQLSTATE[HY000] [2002] Connection refused`
  - Causa: MySQL no esta corriendo o los datos de conexion son incorrectos.
  - Solucion: inicia MySQL y revisa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

- Problema: `SyncBlockchainJob` no aparece o no avanza
  - Causa: el scheduler no esta corriendo.
  - Solucion: ejecuta `php artisan schedule:work`.

- Problema: `401 Unauthorized` al revocar un token
  - Causa: falta el header `Authorization: Bearer {token}` en `DELETE /api/auth/token`.
  - Solucion: obtiene un token con `POST /api/auth/token` y usalo para la revocacion.

## 9. Ejecucion de pruebas

Ejecuta toda la suite con:

```bash
php artisan test
```

Las pruebas no requieren un nodo Bitcoin activo ni una base de datos poblada con datos reales.
Usan mocks del servicio RPC y una base sqlite en memoria para testing.
La suite cubre autenticacion, nodos, bloques, transacciones, mempool y sincronizacion del blockchain.

## 10. Estado funcional actual

En este punto el proyecto ya tiene completas las fases 1 a 8 del plan.
La fase 9 corresponde justamente a esta documentacion.
