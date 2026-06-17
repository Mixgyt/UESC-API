# Plan de Proyecto: UESC-API (Laravel)

> **Objetivo**: Construir una REST API en Laravel que sirva como interfaz entre un nodo Bitcoin Core en modo `regtest` y una aplicacion movil, exponiendo bloques, transacciones y un estado de mempool simplificado.

---

## Indice

1. [Contexto y arquitectura general](#1-contexto-y-arquitectura-general)
2. [Stack tecnologico](#2-stack-tecnologico)
3. [Estructura de carpetas del proyecto](#3-estructura-de-carpetas-del-proyecto)
4. [Fases de desarrollo](#4-fases-de-desarrollo)
   - [Fase 1 - Configuracion base](#fase-1--configuracion-base)
   - [Fase 2 - Capa de comunicacion con el nodo](#fase-2--capa-de-comunicacion-con-el-nodo)
   - [Fase 3 - Endpoints de bloques](#fase-3--endpoints-de-bloques)
   - [Fase 4 - Endpoints de transacciones](#fase-4--endpoints-de-transacciones)
   - [Fase 5 - Mini mempool](#fase-5--mini-mempool)
   - [Fase 6 - Sincronizacion en background](#fase-6--sincronizacion-en-background)
   - [Fase 7 - Autenticacion y seguridad](#fase-7--autenticacion-y-seguridad)
   - [Fase 8 - Pruebas](#fase-8--pruebas)
   - [Fase 9 - DOCS.md para usuario humano](#fase-9--docsmd-para-usuario-humano)
5. [Contratos de API (endpoints)](#5-contratos-de-api-endpoints)
6. [Esquema de base de datos](#6-esquema-de-base-de-datos)
7. [Variables de entorno requeridas](#7-variables-de-entorno-requeridas)
8. [Criterios de aceptacion por fase](#8-criterios-de-aceptacion-por-fase)

---

## 1. Contexto y arquitectura general

```
+------------------+        RPC (HTTP)        +--------------------+
|   Bitcoin Core   | <----------------------> |   Laravel API      |
|   (regtest)      |                          |                    |
|   puerto 18443   |                          |  - BitcoinRpcClient|
+------------------+                          |  - Jobs / Queue    |
                                              |  - Cache (MySQL)   |
                                              |  - MySQL           |
                                              +--------+-----------+
                                                       |  REST JSON
                                                       v
                                              +--------------------+
                                              |   App Movil        |
                                              |  (Android / iOS)   |
                                              +--------------------+
```

**Flujo principal**:
- Laravel se comunica con el nodo via JSON-RPC (usuario y contrasena de `bitcoin.conf`).
- Un Job en cola sincroniza bloques y transacciones periodicamente hacia la base de datos local.
- La app movil consume unicamente la REST API de Laravel (nunca se conecta al nodo directamente).
- MySQL actua como base de datos principal, broker de cola (driver `database`) y almacen de cache (driver `database`). No se requiere Redis ni ninguna dependencia externa adicional.

---

## 2. Stack tecnologico

| Componente       | Tecnologia                              | Version minima |
|------------------|-----------------------------------------|----------------|
| Lenguaje         | PHP                                     | 8.2            |
| Framework        | Laravel                                 | 11.x           |
| Base de datos    | MySQL                                   | 8.0            |
| Cache            | Driver `database` de Laravel (MySQL)    | -              |
| Cola de jobs     | Driver `database` de Laravel (MySQL)    | -              |
| Comunicacion RPC | `denpamusic/php-bitcoinrpc`             | -              |
| Autenticacion    | Laravel Sanctum (tokens)                | -              |
| Testing          | PHPUnit + Pest                          | -              |
| Servidor dev     | `php artisan serve`                     | -              |

**Nota sobre cache y cola**: Laravel incluye soporte nativo para usar MySQL como driver de cache y de cola mediante las tablas `cache` y `jobs`. Esto elimina la necesidad de instalar y mantener Redis. Para el volumen de datos de un nodo regtest, el driver `database` es completamente suficiente.

---

## 3. Estructura de carpetas del proyecto

```
app/
    Console/
        Commands/
            SyncBlockchainCommand.php      # Comando artisan para sincronizacion manual
    Http/
        Controllers/Api/
            BlockController.php
            TransactionController.php
            MempoolController.php
            NodeController.php             # Info general del nodo
        Resources/
            BlockResource.php
            TransactionResource.php
            MempoolEntryResource.php
    Jobs/
        SyncBlockchainJob.php              # Job de sincronizacion en background
    Models/
        Block.php
        Transaction.php
        MempoolEntry.php
    Services/
        BitcoinRpcService.php              # Wrapper del cliente RPC
database/
    migrations/
        xxxx_create_blocks_table.php
        xxxx_create_transactions_table.php
        xxxx_create_mempool_entries_table.php
    seeders/
routes/
    api.php
config/
    bitcoin.php                            # Configuracion del nodo RPC
DOCS.md                                    # Documentacion para el usuario humano (Fase 9)
```

---

## 4. Fases de desarrollo

---

### Fase 1 - Configuracion base

**Tareas**:

1. Crear proyecto Laravel:
   ```bash
   composer create-project laravel/laravel uesc-api
   cd uesc-api
   ```

2. Instalar dependencias:
   ```bash
   composer require denpamusic/php-bitcoinrpc
   composer require guzzlehttp/guzzle
   composer require laravel/sanctum
   ```

3. Crear archivo `config/bitcoin.php`:
   ```php
   <?php
   return [
       'host'     => env('BITCOIN_RPC_HOST', '127.0.0.1'),
       'port'     => env('BITCOIN_RPC_PORT', 18443),
       'user'     => env('BITCOIN_RPC_USER', 'regtest'),
       'password' => env('BITCOIN_RPC_PASSWORD', 'regtest'),
       'wallet'   => env('BITCOIN_RPC_WALLET', ''),
   ];
   ```

4. Configurar `.env` con la conexion MySQL y los drivers de cache y cola:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bitcoin_api
   DB_USERNAME=root
   DB_PASSWORD=secret

   CACHE_STORE=database
   QUEUE_CONNECTION=database
   ```

5. Crear las tablas de infraestructura de Laravel (cache, jobs, sesiones):
   ```bash
   php artisan cache:table
   php artisan queue:table
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```
   Esto genera y ejecuta las migraciones para `cache`, `jobs`, y las tablas de Sanctum, todas sobre MySQL.

6. Verificar que la base de datos esta accesible y las migraciones corrieron sin error.

**Criterio de aceptacion**: `php artisan serve` inicia sin errores; `php artisan migrate:status` muestra todas las migraciones de infraestructura como ejecutadas.

---

### Fase 2 - Capa de comunicacion con el nodo

**Archivo**: `app/Services/BitcoinRpcService.php`

**Tareas**:

1. Crear `BitcoinRpcService` que encapsule todas las llamadas RPC. Metodos requeridos:

   | Metodo del servicio                          | Comando RPC interno  |
   |----------------------------------------------|----------------------|
   | `getBlockchainInfo()`                        | `getblockchaininfo`  |
   | `getBlockCount()`                            | `getblockcount`      |
   | `getBlockHash(int $height)`                  | `getblockhash`       |
   | `getBlock(string $hash, int $verbosity = 2)` | `getblock`           |
   | `getRawTransaction(string $txid, bool $verbose = true)` | `getrawtransaction` |
   | `getMempoolInfo()`                           | `getmempoolinfo`     |
   | `getRawMempool(bool $verbose = true)`        | `getrawmempool`      |
   | `getMempoolEntry(string $txid)`              | `getmempoolentry`    |
   | `getNetworkInfo()`                           | `getnetworkinfo`     |

2. Registrar el servicio como Singleton en `AppServiceProvider`:
   ```php
   $this->app->singleton(BitcoinRpcService::class, fn() => new BitcoinRpcService());
   ```

3. Manejar excepciones de conexion con `try/catch` y lanzar `HttpException` con codigo 503 si el nodo no responde.

4. Para respuestas que no cambian frecuentemente (por ejemplo `getNetworkInfo`), usar `Cache::remember()` con el driver `database`:
   ```php
   return Cache::remember('node.network_info', 60, fn() => $this->rpc->getnetworkinfo());
   ```

**Criterio de aceptacion**: Ejecutar `php artisan tinker` y llamar `app(BitcoinRpcService::class)->getBlockCount()` retorna un entero.

---

### Fase 3 - Endpoints de bloques

**Archivos**: `BlockController.php`, `BlockResource.php`, migracion y modelo `Block`.

**Tareas**:

1. Crear migracion `blocks`:
   - `hash` (string 64, primary key)
   - `height` (unsignedInteger, unique, index)
   - `time` (timestamp)
   - `tx_count` (unsignedInteger, default 0)
   - `size` (unsignedInteger, default 0)
   - `weight` (unsignedInteger, default 0)
   - `difficulty` (double)
   - `miner_reward` (unsignedBigInteger, nullable) -- en satoshis
   - `raw` (json) -- bloque completo cacheado desde el nodo

2. Crear modelo `Block` con sus `$fillable` y relacion `hasMany(Transaction::class, 'block_hash', 'hash')`.

3. Crear `BlockController` con los metodos:
   - `index()` -- lista paginada de bloques, orden descendente por height
   - `show(string $hashOrHeight)` -- detecta si el parametro es un hash (64 chars) o un entero y busca en consecuencia
   - `latest()` -- equivale a `Block::orderByDesc('height')->first()`

4. Crear `BlockResource` que formatee la respuesta JSON:
   - Excluir el campo `raw` de la respuesta por defecto
   - Incluir `transactions` como array de txids (extraido del campo `raw`)

5. Registrar rutas en `routes/api.php`:
   ```
   GET /api/blocks
   GET /api/blocks/latest
   GET /api/blocks/{hashOrHeight}
   ```

**Criterio de aceptacion**: `GET /api/blocks/latest` retorna el bloque mas reciente minado en regtest con sus campos formateados y sin exponer el campo `raw`.

---

### Fase 4 - Endpoints de transacciones

**Archivos**: `TransactionController.php`, `TransactionResource.php`, migracion y modelo `Transaction`.

**Tareas**:

1. Crear migracion `transactions`:
   - `txid` (string 64, primary key)
   - `block_hash` (string 64, foreign key nullable -- null si esta en mempool)
   - `block_height` (unsignedInteger, nullable, index)
   - `confirmed_at` (timestamp, nullable)
   - `fee` (unsignedBigInteger, nullable) -- satoshis
   - `size` (unsignedInteger)
   - `vsize` (unsignedInteger)
   - `input_count` (unsignedInteger)
   - `output_count` (unsignedInteger)
   - `total_output_sat` (unsignedBigInteger)
   - `raw` (json) -- tx completa

2. Crear modelo `Transaction` con relacion `belongsTo(Block::class, 'block_hash', 'hash')`.

3. Crear `TransactionController` con metodos:
   - `index()` -- lista paginada de transacciones confirmadas, orden descendente por `confirmed_at`
   - `show(string $txid)` -- detalle de una transaccion; si no existe en BD buscarla en mempool via RPC
   - `byBlock(string $hashOrHeight)` -- transacciones de un bloque especifico

4. Crear `TransactionResource` con los campos:
   - `txid`, `status` (confirmed / unconfirmed), `fee_sat`, `fee_rate_sat_vbyte`
   - `size`, `vsize`, `input_count`, `output_count`
   - `confirmed_at`, `block_height`, `block_hash`
   - `inputs` y `outputs` extraidos del campo `raw` (nunca retornar `raw` directo)

5. Registrar rutas:
   ```
   GET /api/transactions
   GET /api/transactions/{txid}
   GET /api/blocks/{hashOrHeight}/transactions
   ```

**Criterio de aceptacion**: `GET /api/transactions/{txid}` retorna detalles correctos incluyendo inputs y outputs con valores en satoshis.

---

### Fase 5 - Mini mempool

**Archivos**: `MempoolController.php`, `MempoolEntryResource.php`, migracion y modelo `MempoolEntry`.

**Tareas**:

1. Crear migracion `mempool_entries`:
   - `txid` (string 64, primary key)
   - `fee` (unsignedBigInteger) -- satoshis
   - `vsize` (unsignedInteger)
   - `fee_rate` (double) -- sat/vbyte, calculado al insertar como `fee / vsize`
   - `depends` (json) -- array de txids de los que depende esta tx
   - `time` (timestamp) -- cuando entro al mempool segun el nodo
   - `raw` (json) -- entrada completa devuelta por `getrawmempool`

2. Crear modelo `MempoolEntry`.

3. Crear `MempoolController` con metodos:
   - `index()` -- lista paginada, orden descendente por `fee_rate`; soportar query param `?sort=fee_rate|time&order=asc|desc`
   - `show(string $txid)` -- detalle de una entrada
   - `summary()` -- estadisticas calculadas con agregaciones SQL: `COUNT`, `SUM(fee)`, `MIN/MAX/AVG(fee_rate)`, `SUM(vsize)`

4. Crear `MempoolEntryResource` con campos: `txid`, `fee_sat`, `vsize`, `fee_rate_sat_vbyte`, `depends`, `time`.

5. Registrar rutas:
   ```
   GET /api/mempool
   GET /api/mempool/summary
   GET /api/mempool/{txid}
   ```

**Criterio de aceptacion**: `GET /api/mempool/summary` retorna conteo de transacciones pendientes y estadisticas de fees calculadas desde MySQL en tiempo real.

---

### Fase 6 - Sincronizacion en background

**Archivos**: `SyncBlockchainJob.php`, `SyncBlockchainCommand.php`, scheduler en `routes/console.php`.

**Tareas**:

1. Crear `SyncBlockchainJob` con la siguiente logica en su metodo `handle()`:

   a. Obtener `getBlockCount()` del nodo.
   b. Obtener el mayor `height` registrado en la tabla `blocks` (`Block::max('height') ?? -1`).
   c. Para cada altura faltante (de `max_local + 1` hasta `node_count`):
      - Obtener el hash con `getBlockHash($height)`.
      - Obtener el bloque completo con `getBlock($hash, 2)`.
      - Insertar el bloque en `blocks` (usar `firstOrCreate` para idempotencia).
      - Para cada transaccion del bloque, insertar en `transactions` (usar `upsert` con `txid` como llave).
   d. Refrescar la tabla `mempool_entries`:
      - Obtener `getRawMempool(true)`.
      - Usar `MempoolEntry::upsert()` con todos los campos.
      - Eliminar de `mempool_entries` cualquier txid que ya exista en `transactions` (fue confirmada).

2. El job debe implementar la interfaz `ShouldBeUnique` de Laravel para evitar ejecuciones solapadas:
   ```php
   class SyncBlockchainJob implements ShouldQueue, ShouldBeUnique { ... }
   ```
   La unicidad se gestiona a traves de la tabla `job_batches` o `cache` en MySQL, sin Redis.

3. Crear comando Artisan `blockchain:sync` que ejecute el job de forma sincrona:
   ```bash
   php artisan blockchain:sync
   ```

4. Registrar el job en el scheduler para ejecutarse cada 30 segundos en `routes/console.php`:
   ```php
   Schedule::job(new SyncBlockchainJob)->everyThirtySeconds();
   ```

5. Iniciar el worker de cola (terminal separada):
   ```bash
   php artisan queue:work --sleep=3 --tries=3
   ```

6. Iniciar el scheduler (terminal separada):
   ```bash
   php artisan schedule:work
   ```

**Criterio de aceptacion**: Minar un bloque en regtest con `bitcoin-cli -regtest generatetoaddress 1 <addr>` y verificar que aparece en `GET /api/blocks` dentro de los siguientes 30 segundos sin intervencion manual.

---

### Fase 7 - Autenticacion y seguridad

**Tareas**:

1. Confirmar que Sanctum esta publicado y sus migraciones ejecutadas (realizado en Fase 1).

2. Crear seeder `ApiUserSeeder` que genere un usuario y un token de acceso por defecto para desarrollo:
   ```php
   $user = User::create(['name' => 'Admin', 'email' => 'admin@regtest.local', 'password' => bcrypt('secret')]);
   $token = $user->createToken('default')->plainTextToken;
   // Imprimir el token en consola para que el desarrollador lo use
   $this->command->info("Token: {$token}");
   ```

3. Proteger todos los endpoints de la API bajo el middleware `auth:sanctum`:
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       // todas las rutas de bloques, transacciones, mempool y nodo
   });
   ```

4. Agregar throttling diferenciado por grupo de rutas:
   - Endpoints de mempool y nodo: `throttle:60,1` (60 solicitudes por minuto)
   - Endpoints de bloques y transacciones: `throttle:120,1`

5. Agregar headers de seguridad HTTP en `bootstrap/app.php` mediante un middleware global:
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: DENY`

6. Crear los endpoints de autenticacion (estos no requieren token previo):
   ```
   POST   /api/auth/token    -- recibe {email, password}, retorna plainTextToken
   DELETE /api/auth/token    -- revoca el token del usuario autenticado
   ```

**Criterio de aceptacion**: Un request sin token a `GET /api/blocks` retorna `401 Unauthorized`. Con token valido en el header `Authorization: Bearer {token}` retorna `200` con datos.

---

### Fase 8 - Pruebas

**Tareas**:

1. Instalar Pest si no esta incluido por defecto:
   ```bash
   composer require pestphp/pest --dev
   php artisan pest:install
   ```

2. Crear un `TestCase` base que mockee `BitcoinRpcService` para que los tests no dependan de un nodo activo:
   ```php
   protected function mockRpc(): void
   {
       $this->mock(BitcoinRpcService::class, function ($mock) {
           $mock->shouldReceive('getBlockCount')->andReturn(10);
           $mock->shouldReceive('getBlockchainInfo')->andReturn([...]);
           // etc.
       });
   }
   ```

3. Escribir tests de Feature para cada grupo de endpoints:

   - `BlockApiTest`:
     - `GET /api/blocks` retorna lista paginada con estructura correcta
     - `GET /api/blocks/latest` retorna el bloque de mayor height
     - `GET /api/blocks/{height}` retorna bloque correcto por altura
     - `GET /api/blocks/{hash}` retorna bloque correcto por hash
     - Request sin token retorna 401

   - `TransactionApiTest`:
     - `GET /api/transactions/{txid}` con txid valido retorna campos esperados
     - `GET /api/transactions/{txid}` con txid inexistente retorna 404
     - `GET /api/blocks/{height}/transactions` retorna solo las txs de ese bloque

   - `MempoolApiTest`:
     - `GET /api/mempool` retorna lista ordenada por fee_rate
     - `GET /api/mempool/summary` retorna todos los campos estadisticos
     - `GET /api/mempool/{txid}` con txid no encontrado retorna 404

   - `AuthApiTest`:
     - `POST /api/auth/token` con credenciales correctas retorna token
     - `POST /api/auth/token` con credenciales incorrectas retorna 422
     - `DELETE /api/auth/token` revoca el token activo

4. Ejecutar la suite completa:
   ```bash
   php artisan test
   ```

**Criterio de aceptacion**: `php artisan test` pasa al 100% sin dependencia de nodo externo ni de Redis.

---

### Fase 9 - DOCS.md para usuario humano

**Archivo de salida**: `DOCS.md` en la raiz del proyecto.

Esta es la fase final. El agente debe redactar el archivo `DOCS.md` con todo lo necesario para que un humano pueda instalar, configurar y usar la API sin haber leido el codigo fuente. El archivo debe estar completamente en texto plano, sin emojis, sin caracteres especiales fuera del ASCII basico, y sin simbolos decorativos.

**Estructura obligatoria del DOCS.md**:

#### 1. Requisitos del sistema
Listar con version exacta o minima cada prerequisito necesario:
- PHP (version minima)
- Composer
- MySQL
- Bitcoin Core con soporte para regtest
- Extension PHP requeridas: `ext-json`, `ext-curl`, `ext-mbstring`, `ext-pdo_mysql`

#### 2. Configuracion del nodo Bitcoin
Explicar como debe estar configurado el archivo `bitcoin.conf` para que el nodo acepte conexiones RPC desde Laravel. Incluir el bloque de configuracion completo con los parametros `regtest=1`, `server=1`, `rpcuser`, `rpcpassword`, `rpcport`, y `rpcallowip`. Indicar donde se ubica ese archivo en Linux, macOS y Windows.

Incluir los comandos para iniciar el nodo y crear una wallet en regtest:
```bash
bitcoind -regtest -daemon
bitcoin-cli -regtest createwallet "miwallet"
bitcoin-cli -regtest getnewaddress
bitcoin-cli -regtest generatetoaddress 101 <direccion>
```
Explicar por que se minan 101 bloques (madurez de coinbase).

#### 3. Instalacion del proyecto
Paso a paso desde cero:
```bash
git clone <repositorio>
cd bitcoin-regtest-api
composer install
cp .env.example .env
php artisan key:generate
```
Describir cada variable del `.env` que el usuario debe editar y por que.

#### 4. Configuracion de la base de datos
Explicar como crear la base de datos en MySQL:
```sql
CREATE DATABASE bitcoin_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Luego ejecutar las migraciones y el seeder:
```bash
php artisan migrate
php artisan db:seed --class=ApiUserSeeder
```
Indicar que el seeder imprime el token de acceso en consola y que el usuario debe copiarlo.

#### 5. Ejecucion de los procesos
Indicar que son necesarios tres procesos corriendo simultaneamente en terminales separadas y explicar el rol de cada uno:

Terminal 1 - Servidor HTTP:
```bash
php artisan serve
```

Terminal 2 - Worker de cola (sincronizacion de bloques):
```bash
php artisan queue:work --sleep=3 --tries=3
```

Terminal 3 - Scheduler (disparo periodico del job):
```bash
php artisan schedule:work
```

Explicar que el scheduler dispara `SyncBlockchainJob` cada 30 segundos, que el worker lo procesa, y que el resultado es visible en los endpoints sin recargar manualmente.

#### 6. Uso de la API
Documentar cada endpoint con:
- Metodo HTTP y ruta
- Descripcion en una oracion
- Header requerido: `Authorization: Bearer {token}`
- Parametros de query disponibles (si aplica)
- Ejemplo de request con `curl`
- Ejemplo de respuesta JSON completa y real (no truncada)

Cubrir obligatoriamente:
- `POST /api/auth/token`
- `GET /api/node/info`
- `GET /api/blocks`
- `GET /api/blocks/latest`
- `GET /api/blocks/{hashOrHeight}`
- `GET /api/transactions`
- `GET /api/transactions/{txid}`
- `GET /api/blocks/{hashOrHeight}/transactions`
- `GET /api/mempool`
- `GET /api/mempool/summary`
- `GET /api/mempool/{txid}`

#### 7. Sincronizacion manual
Explicar el comando para forzar una sincronizacion inmediata sin esperar el scheduler:
```bash
php artisan blockchain:sync
```
Indicar cuando es util (primer arranque, nodo reiniciado, debugging).

#### 8. Solucion de problemas comunes
Documentar al menos los siguientes casos con causa y solucion:

- La API retorna `503 Service Unavailable`: el nodo no esta corriendo o las credenciales RPC en `.env` son incorrectas.
- Los endpoints retornan datos vacios despues de minar bloques: el worker de cola no esta corriendo; ejecutar `php artisan queue:work`.
- Error `SQLSTATE[HY000] [2002] Connection refused`: MySQL no esta corriendo o los datos de conexion en `.env` son incorrectos.
- El job `SyncBlockchainJob` no aparece en la tabla `jobs`: el scheduler no esta corriendo; ejecutar `php artisan schedule:work`.
- `401 Unauthorized` en todos los endpoints: falta el header `Authorization: Bearer {token}`; obtener el token con `POST /api/auth/token`.

#### 9. Ejecucion de pruebas
```bash
php artisan test
```
Aclarar que las pruebas no requieren nodo Bitcoin activo ni MySQL poblado; usan mocks internos.

**Criterio de aceptacion**: El archivo `DOCS.md` existe en la raiz del proyecto, no contiene emojis ni caracteres fuera de ASCII, y un desarrollador sin contexto previo puede seguirlo de inicio a fin para tener la API funcionando.

---

## 5. Contratos de API (endpoints)

### Autenticacion
```
POST /api/auth/token
```
Body: `{ "email": "admin@regtest.local", "password": "secret" }`
Respuesta: `{ "token": "1|abcdef..." }`

### Informacion del nodo
```
GET /api/node/info
```
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

### Bloques
```
GET /api/blocks?page=1&per_page=20
GET /api/blocks/latest
GET /api/blocks/{hash_o_height}
```
```json
{
  "data": {
    "hash": "00000...",
    "height": 142,
    "time": "2024-01-15T10:30:00Z",
    "tx_count": 3,
    "size": 1024,
    "weight": 4096,
    "difficulty": 4.65e-10,
    "miner_reward_sat": 5000000000,
    "transactions": ["txid1", "txid2", "txid3"]
  }
}
```

### Transacciones
```
GET /api/transactions?page=1
GET /api/transactions/{txid}
GET /api/blocks/{hash_o_height}/transactions
```
```json
{
  "data": {
    "txid": "abc123...",
    "status": "confirmed",
    "block_height": 142,
    "block_hash": "00000...",
    "confirmed_at": "2024-01-15T10:30:00Z",
    "fee_sat": 1000,
    "fee_rate_sat_vbyte": 2.5,
    "size": 400,
    "vsize": 400,
    "input_count": 1,
    "output_count": 2,
    "inputs": [
      { "txid": "prev...", "vout": 0, "value_sat": 5000000000 }
    ],
    "outputs": [
      { "address": "bcrt1q...", "value_sat": 4999999000 },
      { "address": "bcrt1q...", "value_sat": 1000 }
    ]
  }
}
```

### Mempool
```
GET /api/mempool?sort=fee_rate&order=desc&page=1
GET /api/mempool/summary
GET /api/mempool/{txid}
```
```json
{
  "data": {
    "tx_count": 5,
    "total_size_vbytes": 2048,
    "fee_min_sat_vbyte": 1.0,
    "fee_max_sat_vbyte": 10.0,
    "fee_avg_sat_vbyte": 4.2,
    "total_fees_sat": 8600
  }
}
```

---

## 6. Esquema de base de datos

```sql
-- bloques minados
CREATE TABLE blocks (
  hash           VARCHAR(64)      NOT NULL PRIMARY KEY,
  height         INT UNSIGNED     NOT NULL UNIQUE,
  time           DATETIME         NOT NULL,
  tx_count       INT UNSIGNED     NOT NULL DEFAULT 0,
  size           INT UNSIGNED     NOT NULL DEFAULT 0,
  weight         INT UNSIGNED     NOT NULL DEFAULT 0,
  difficulty     DOUBLE           NOT NULL DEFAULT 0,
  miner_reward   BIGINT UNSIGNED  NULL,
  raw            JSON             NULL,
  created_at     TIMESTAMP        NULL,
  updated_at     TIMESTAMP        NULL,
  INDEX idx_height (height)
);

-- transacciones confirmadas
CREATE TABLE transactions (
  txid              VARCHAR(64)      NOT NULL PRIMARY KEY,
  block_hash        VARCHAR(64)      NULL,
  block_height      INT UNSIGNED     NULL,
  confirmed_at      DATETIME         NULL,
  fee               BIGINT UNSIGNED  NULL,
  size              INT UNSIGNED     NOT NULL DEFAULT 0,
  vsize             INT UNSIGNED     NOT NULL DEFAULT 0,
  input_count       INT UNSIGNED     NOT NULL DEFAULT 0,
  output_count      INT UNSIGNED     NOT NULL DEFAULT 0,
  total_output_sat  BIGINT UNSIGNED  NOT NULL DEFAULT 0,
  raw               JSON             NULL,
  created_at        TIMESTAMP        NULL,
  updated_at        TIMESTAMP        NULL,
  CONSTRAINT fk_tx_block FOREIGN KEY (block_hash) REFERENCES blocks(hash),
  INDEX idx_block_hash  (block_hash),
  INDEX idx_block_height (block_height)
);

-- transacciones pendientes (mempool)
CREATE TABLE mempool_entries (
  txid        VARCHAR(64)      NOT NULL PRIMARY KEY,
  fee         BIGINT UNSIGNED  NOT NULL DEFAULT 0,
  vsize       INT UNSIGNED     NOT NULL DEFAULT 0,
  fee_rate    DOUBLE           NOT NULL DEFAULT 0,
  depends     JSON             NULL,
  time        DATETIME         NOT NULL,
  raw         JSON             NULL,
  created_at  TIMESTAMP        NULL,
  updated_at  TIMESTAMP        NULL,
  INDEX idx_fee_rate (fee_rate)
);

-- tablas de infraestructura de Laravel (generadas por artisan)
-- jobs          : cola de trabajos en background
-- cache         : almacen de cache de la aplicacion
-- personal_access_tokens : tokens de Sanctum
```

---

## 7. Variables de entorno requeridas

```env
# Aplicacion
APP_NAME="UESC-API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de datos MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitcoin_api
DB_USERNAME=root
DB_PASSWORD=secret

# Cache y cola -- ambos usan MySQL, sin Redis
CACHE_STORE=database
QUEUE_CONNECTION=database

# Nodo Bitcoin Core en modo regtest
BITCOIN_RPC_HOST=127.0.0.1
BITCOIN_RPC_PORT=18443
BITCOIN_RPC_USER=regtest
BITCOIN_RPC_PASSWORD=regtest
BITCOIN_RPC_WALLET=
```

---

## 8. Criterios de aceptacion por fase

| Fase | Criterio verificable |
|------|----------------------|
| 1 - Config base       | `php artisan migrate:status` muestra todas las migraciones de infraestructura ejecutadas |
| 2 - RPC Service       | `app(BitcoinRpcService::class)->getBlockCount()` retorna un entero en tinker |
| 3 - Bloques           | `GET /api/blocks/latest` retorna hash y height del bloque mas reciente sin exponer `raw` |
| 4 - Transacciones     | `GET /api/transactions/{txid}` retorna inputs y outputs con valores en satoshis |
| 5 - Mempool           | `GET /api/mempool/summary` retorna conteo y estadisticas de fees calculadas en MySQL |
| 6 - Sincronizacion    | Minar 1 bloque en regtest; aparece en la API dentro de 30 segundos automaticamente |
| 7 - Autenticacion     | Sin token retorna 401; con token en header retorna 200 con datos |
| 8 - Pruebas           | `php artisan test` pasa al 100% sin nodo activo ni Redis |
| 9 - DOCS.md           | Archivo existe, sin emojis ni caracteres especiales, permite instalar y usar la API desde cero |

---

## Notas para el agente

- Orden de ejecucion estricto: las fases deben ejecutarse en secuencia. La Fase 6 depende de que las Fases 3, 4 y 5 esten completas.
- No usar Redis en ninguna parte del proyecto. El driver de cache y el de cola deben ser siempre `database`.
- No hardcodear credenciales RPC; siempre leer de `config('bitcoin.*')`.
- Todos los valores monetarios deben almacenarse y retornarse en satoshis (entero sin signo), nunca en BTC flotante.
- El campo `raw` en cada tabla sirve como copia local del JSON del nodo; nunca retornarlo directamente en las respuestas de la API.
- Para regtest, las direcciones comienzan con `bcrt1` (bech32) o `m` / `n` (legacy P2PKH). Tenerlo en cuenta al parsear outputs.
- El job de sincronizacion debe ser idempotente: si un bloque ya existe en la base de datos, no sobreescribir ni duplicar.
- Usar `upsert()` de Eloquent para `mempool_entries` al refrescar, con `txid` como llave de conflicto.
- La implementacion de `ShouldBeUnique` en el job usa la tabla `cache` de MySQL para el lock; asegurarse de que dicha tabla exista antes de despachar el job.
- El archivo `DOCS.md` de la Fase 9 debe escribirse en prosa clara, sin listas anidadas excesivas, y debe poder leerse linealmente de arriba a abajo siguiendo los pasos en orden.
