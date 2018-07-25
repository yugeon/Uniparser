<?php

namespace Yugeon\Uniparser\DataStore;


/**
 * Description of ParsedUrlsModel
 *
 * @author yugeon
 */
class ParsedUrlsModel extends \Illuminate\Database\Eloquent\Model {

    const PARSED_URL_STATUS_PENDING = 0;
    const PARSED_URL_STATUS_IN_PROCESS = 1;
    const PARSED_URL_STATUS_COMPLETED = 2;

    function __construct() {
        parent::__construct();
        $this->status = self::PARSED_URL_STATUS_PENDING;
    }

    static $tableName = 'parsed_urls';
    protected $table = 'parsed_urls';

    public function markAsCompleted() {
        $this->status = self::PARSED_URL_STATUS_COMPLETED;
    }

    public function setData($data) {
        $this->data = json_encode($data);
    }

}
