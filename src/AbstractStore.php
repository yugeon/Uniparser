<?php

namespace Yugeon\Uniparser;

use Illuminate\Database\Capsule\Manager as Capsule;
/**
 * Description of AbstractStore
 *
 * @author yugeon
 */
class AbstractStore implements AbstractStoreInterface {

    private static $capsule;
    private $connConfig;
    private $isSchemaCreated;

    function __construct($config = '') {
        if ($config) {
            $this->setConnConfig($config['database']);
        }
    }

    public function _initDb($force = false) {
        if ($force || !self::$capsule) {
            self::$capsule = new Capsule;
            self::$capsule->addConnection($this->connConfig);

            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }

        if (!$this->isSchemaCreated) {
            $this->createSchema();
            $this->isSchemaCreated = true;
        }
    }

    public function createSchema() {
        throw new \Exception('Abstract method createSchema not implemented');
    }

    public function getTableName() {
        throw new \Exception('Abstract method getTableName not implemented');
    }

    public function getPdoForTest() {
        return self::$capsule->getConnection()->getPdo();
    }

    public function setConnConfig($connConfig) {
        $this->connConfig = array_merge([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => 'password',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ], $connConfig);

        if ('sqlite' === $this->connConfig['driver'] && ':memory:' !== $this->connConfig['database']) {
            $filename = $this->connConfig['database'];
            if (!file_exists($filename)) {
                $fh = @fopen($filename, 'w+');
                if (false === $fh) {
                    throw new \Exception("Not have permissins write to file: {$filename}");
                } else {
                    fclose($fh);
                }
            } elseif (!is_writeable($filename)) {
                throw new \Exception("Not have permissins write to file: {$filename}");
            }
        }

        return $this;
    }
}
