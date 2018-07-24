<?php

namespace Yugeon\Uniparser\DataStore;

define('PARSED_URL_STATUS_PENDING', 0);
define('PARSED_URL_STATUS_IN_PROCESS', 1);
define('PARSED_URL_STATUS_COMPLETED', 2);

/**
 * Description of ParsedUrlsModel
 *
 * @author yugeon
 */
class ParsedUrlsModel extends \Illuminate\Database\Eloquent\Model {

    function __construct() {
        $this->status = PARSED_URL_STATUS_PENDING;
    }


    static $tableName = 'parsed_urls';
    protected $table = 'parsed_urls';

    public function markAsCompleted() {
        $this->status = PARSED_URL_STATUS_COMPLETED;
    }

    public function setData($data) {
        $this->data = json_encode($data);
    }

}
