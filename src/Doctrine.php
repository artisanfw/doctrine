<?php

namespace Artisan\Services;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;


class Doctrine
{
    const string MODELS_PATH_KEY = 'models_path';

    private static mixed $instance = null;

    private array $dbSettings = [];
    private string $modelsPath = '';

    private function __construct() {}
    private function __clone() {}

    public static function i(): static
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function load(array $dbConf): Doctrine
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
        $paths = array_merge([$this->modelsPath], $paths);
        $attrConf = ORMSetup::createAttributeMetadataConfiguration($paths);
        $connection = DriverManager::getConnection($this->dbSettings, $attrConf);
        return new EntityManager($connection, $attrConf);
    }
}
