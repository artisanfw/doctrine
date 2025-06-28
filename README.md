# Doctrine
A Singleton-based implementation for Doctrine ORM.

This class provides a singleton instance of Doctrine ORM's `EntityManager`, enabling consistent and easy access to ORM functionality throughout the application.

## Instantiating the Service
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

## Writing to the Database
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
