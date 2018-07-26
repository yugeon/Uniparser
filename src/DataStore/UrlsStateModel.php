<?php

namespace Yugeon\Uniparser\DataStore;

/**
 * Description of StateModel
 *
 * @author yugeon
 */
class UrlsStateModel extends \Illuminate\Database\Eloquent\Model {

    const PENDING = 0;
    const PROCESS = 1;
    const COMPLETED = 2;
    const REJECTED = 3;


    static $tableName = 'url_states';
    protected $table = 'url_states';
    protected $fillable = ['try_number', 'url', 'status'];
}
