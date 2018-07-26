<?php

namespace Yugeon\Uniparser;

use Illuminate\Database\Capsule\Manager as Capsule;
use Yugeon\Uniparser\DataStore\ParsedUrlsModel;
/**
 * Description of DataStore
 *
 * @author yugeon
 */
class DataStore extends AbstractStore {

    function __construct($config = '') {
        parent::__construct($config);
    }    

    public function createSchema() {
        if (Capsule::schema()->hasTable($this->getTableName())) {
            return;
        }

        try {
            Capsule::schema()->create($this->getTableName(), function ($table) {
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

    public function getTableName() {
        return ParsedUrlsModel::$tableName;
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
