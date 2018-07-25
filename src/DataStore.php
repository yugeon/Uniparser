<?php

namespace Yugeon\Uniparser;

use Illuminate\Database\Capsule\Manager as Capsule;
use Yugeon\Uniparser\DataStore\ParsedUrlsModel;
/**
 * Description of DataStore
 *
 * @author yugeon
 */
class DataStore {

    private $capsule;
    private $connConfig;

    function __construct($config = '') {
        if ($config) {
            $this->setConnConfig($config['database']);
        }
    }

    public function _initDb() {
        if ($this->capsule) {
            return $this->capsule;
        }

        $this->capsule = new Capsule;
        $this->capsule->addConnection($this->connConfig);
        
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->createSchema();
    }

    public function createSchema() {
        if (Capsule::schema()->hasTable(ParsedUrlsModel::$tableName)) {
            return;
        }

        try {
            Capsule::schema()->create(ParsedUrlsModel::$tableName, function ($table) {
                $table->increments('id');
                $table->string('url')->unique();
                $table->tinyInteger('status');
                $table->json('data');
                $table->timestamps();
            });
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function getPdoForTest() {
        return $this->capsule->getConnection()->getPdo();
    }

    public function getTableName() {
        return ParsedUrlsModel::$tableName;
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

        return $this;
    }

    public function save($url, $data) {
        $this->_initDb();

        $parsedUrlsModel = ParsedUrlsModel::firstOrNew(['url' => $url]);
        $parsedUrlsModel->url = $url;
        $parsedUrlsModel->setData($data);
        $parsedUrlsModel->markAsCompleted();
        return $parsedUrlsModel->save();
    }

}
