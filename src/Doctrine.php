<?php

namespace Artisan\Services;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use RuntimeException;
use Throwable;

class Doctrine
{
    private const string MODELS_PATH_KEY = 'models_path';

    private static ?self $instance = null;

    private array $dbSettings = [];
    private string $modelsPath = '';
    private ?EntityManager $entityManager = null;

    private function __construct() {}
    private function __clone() {}

    public static function i(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function load(array $dbConf): self
    {
        if (isset($dbConf[self::MODELS_PATH_KEY])) {
            $this->modelsPath = $dbConf[self::MODELS_PATH_KEY];
            unset($dbConf[self::MODELS_PATH_KEY]);
        }

        $this->dbSettings = $dbConf;
        return $this;
    }

    public function getEntityManager(?array $paths = []): EntityManager
    {
        if (empty($this->dbSettings)) {
            throw new RuntimeException('Doctrine must be configured using load() before calling getEntityManager().');
        }

        if ($this->entityManager) {
            return $this->entityManager;
        }

        $paths = array_merge([$this->modelsPath], $paths);
        $config = ORMSetup::createAttributeMetadataConfiguration($paths);
        $connection = DriverManager::getConnection($this->dbSettings, $config);

        $this->entityManager = new EntityManager($connection, $config);

        return $this->entityManager;
    }

    /**
     * @throws Exception
     */
    public function query(string $query, array $params = []): int|array
    {
        if ($this->isReadQuery($query)) {
            return $this->getEntityManager()
                ->getConnection()
                ->fetchAllAssociative($query, $params);
        }

        return $this->getEntityManager()
            ->getConnection()
            ->executeStatement($query, $params);
    }

    /**
     * Returns the first row of the result set.
     *
     * @throws Exception
     */
    public function getOne(string $query, array $params = []): ?array
    {
        $results = $this->query($query, $params);
        return $results[0] ?? null;
    }

    /**
     * Returns the first column.
     * Useful for queries like: "SELECT count(*) FROM Users"
     *
     * @throws Exception
     */
    public function getValue(string $query, array $params = []): mixed
    {
        $row = $this->getOne($query, $params);
        return $row ? array_values($row)[0] : null;
    }

    /**
     * @throws Throwable
     */
    public function transactionQuery(callable $callback): mixed
    {
        $conn = $this->getEntityManager()->getConnection();
        return $conn->transactional($callback);
    }

    private function isReadQuery(string $query): bool
    {
        // Remove comments
        $query = preg_replace('/(--|#).*?(\r?\n)|\/\*.*?\*\//s', '', $query);
        $query = ltrim($query);
        $keyword = strtolower(strtok($query, " \t\n\r")); // First word, lowercased

        return in_array($keyword, [
            'select', 'show', 'describe', 'pragma', 'explain', 'with'
        ], true);
    }
}
