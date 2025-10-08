# Thor PDO Table

Lightweight utilities to map plain PHP objects (rows) to SQL tables using PHP 8 attributes, with a thin CRUD helper and optional in-memory caching.

This package builds on trehinos/thor-pdo-extension to provide:
- Declarative table and column metadata via PHP attributes
- A CrudHelper to perform typed CRUD operations and hydrate objects
- Drivers to generate DDL (CREATE TABLE/INDEX) for MySQL and SQLite
- A small write-back cache to batch updates

## Requirements

- PHP 8.1+
- ext-pdo enabled and a PDO driver (mysql or sqlite)
- trehinos/thor-pdo-extension (installed automatically by Composer)

## Installation

Install via Composer:

```bash
composer require trehinos/thor-pdo-table
```

## Core concepts

- Row and RowInterface: a Row represents a table row; hydration/serialization is handled by PdoRowTrait.
- Attributes: use PHP 8 attributes (Table, Column, Index) to describe the table schema.
- Primary keys: single or composite primary keys are supported via the Table(primaryKeys: [...]) declaration.
- Traits: HasIdTrait adds an auto-increment integer id; HasPublicIdTrait adds a GUID-like public_id suitable for exposure.
- CrudHelper: thin helper around ArrayCrud to perform create/read/update/delete and to instantiate rows.

## Quick start

### 1) Define a row class with attributes

```php
use Thor\Database\PdoTable\PdoRow\Row;
use Thor\Database\PdoTable\PdoRow\Attributes\{Table, Column, Index};
use Thor\Database\PdoTable\PdoRow\TableType\{IntegerType, StringType};

#[Table('users', primaryKeys: ['id'], autoColumnName: 'id')]
#[Index(['id'], unique: true)]
abstract class UserRow extends Row
{
    #[Column('id', new IntegerType(), nullable: false)]
    protected ?int $id = null;

    #[Column('username', new StringType(64))]
    protected ?string $username = null;
}
```

Row already provides helper methods through PdoRowTrait to convert to/from arrays.

### 2) Use CrudHelper

```php
use Thor\Database\PdoExtension\Requester;
use Thor\Database\PdoTable\CrudHelper;

$requester = new Requester($pdo); // from thor-pdo-extension
$crud = new CrudHelper(UserRow::class, $requester);

// Create
$user = new class() extends UserRow {};
$user->fromArray(['username' => 'alice']);
$primary = $crud->createOne($user); // returns primary string; if your row extends AbstractRow with public_id, it returns the public_id

// Read one by primary values (order must match Table primaryKeys)
$loaded = $crud->readOne([1]);

// Update
$loaded->fromArray(['username' => 'alice2']);
$crud->updateOne($loaded);

// Delete
$crud->deleteOne($loaded);
```

### 3) Optional cache

```php
use Thor\Database\PdoTable\Cache\Cache;
use Thor\Database\PdoExtension\Criteria;

$cache = new Cache($crud);
$cache->loadAll();                 // warm up cache
$u = $cache->get('1');             // returns associative array or object (depending on CrudHelper)
$cache->set('1', $u);              // mark as pending
$cache->persistAll();              // flush pending changes to DB

// Or load a filtered list
$cache->loadList(Criteria::eq('active', 1));
```

## Schema generation

Use SchemaHelper with a driver (MySQL or SQLite) to create/drop tables from your attributes. Debug mode returns SQL instead of executing it.

```php
use Thor\Database\PdoExtension\Requester;
use Thor\Database\PdoTable\SchemaHelper;
use Thor\Database\PdoTable\Driver\{MySqlDriver, SqliteDriver};

$requester = new Requester($pdo);
$driver = new MySqlDriver(); // or new SqliteDriver()
$schema = new SchemaHelper($requester, $driver, UserRow::class, isDebug: false);

// Create table and indexes
$schema->createTable();

// Drop table and indexes
$schema->dropTable();

// Debug mode (no execution, returns SQL string)
$schemaDebug = new SchemaHelper($requester, $driver, UserRow::class, isDebug: true);
$sql = $schemaDebug->createTable();
```

## Drivers

Two drivers help you generate DDL from your attributes:
- MySql: generates CREATE TABLE and related statements for MySQL
- Sqlite: generates CREATE TABLE and CREATE INDEX for SQLite

Both rely on AttributesReader to parse your row class.

## Tips and gotchas

- Composite keys: pass ordered values to CrudHelper::readOne([...]) in the same order as declared in Table(primaryKeys: [...]).
- Public ID: when using HasPublicIdTrait, remember to call generatePublicId() before insert if you need a deterministic public_id.
- Requester access: CrudHelper::getRequester() lets you run custom queries alongside CRUD operations.

## Development

- PHP 8.1+
- Run tests with PHPUnit if present in your project
- This package depends on trehinos/thor-pdo-extension

## License

MIT Copyright (c) 2021-2025 Sébastien Geldreich