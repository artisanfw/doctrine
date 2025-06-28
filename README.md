# Doctrine
A Singleton-based implementation for Doctrine ORM.

This class provides a singleton instance of Doctrine ORM's `EntityManager`, enabling consistent and easy access to ORM functionality throughout the application. It also includes lightweight helpers for raw SQL execution with automatic read/write detection.

## Using the EntityManager (ORM-style)

### Instantiating the Service
```php
$dbParams = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => '',
    'user' => '',
    'password' => '',
    'models_path' => PROJECT_DIR . '/src/Models',
];

$doctrine = Doctrine::i()->load($dbParams));
```

### Writing to the Database
```php
$user = new User();
// set properties

$em = Doctrine::i()->getEntityManager();
$em->persist($user);
$em->flush();
```

## Reading from the Database
```php
$em = Doctrine::i()->getEntityManager();
$user = $em->getRepository(User::class)->findOneBy(['email' => 'test@email.com']);
```

## Executing Raw SQL

### Auto-detected Read or Write Query
```php
// SELECT query (returns an array of rows)
$rows = Doctrine::i()->query("SELECT * FROM Users WHERE status = ?", ['active']);

// INSERT/UPDATE/DELETE (returns affected row count)
$affected = Doctrine::i()->query("UPDATE Users SET status = :status WHERE last_login < :date", ['status' => 'active', 'date' => 2024-01-01']);
```
### Fetch a Single Row
```php
$user = Doctrine::i()->getOne("SELECT * FROM Users WHERE id = ?", [5]);
// Returns null if no match
```
### Fetch a Single Scalar Value
```php
$count = Doctrine::i()->getValue("SELECT COUNT(*) FROM Users WHERE status = :status AND created_at > :date", ['status' => 'active', 'date' => '2025-09-15']);
```
### Run a Transaction
```php
Doctrine::i()->transactionQuery(function($conn) {
    $conn->executeStatement("UPDATE Users SET balance = balance - 100 WHERE id = ?", [1]);
    $conn->executeStatement("UPDATE Users SET balance = balance + 100 WHERE id = ?", [2]);
});
```

**Note:** You can use either positional (`?`) or named (`:param`) placeholders in your SQL queries, but do not mix both styles in the same statement.
