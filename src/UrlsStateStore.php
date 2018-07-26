<?php

namespace Yugeon\Uniparser;

use Illuminate\Database\Capsule\Manager as Capsule;
use Yugeon\Uniparser\DataStore\UrlsStateModel;

/**
 * Description of StateStore
 *
 * @author yugeon
 */
class UrlsStateStore extends AbstractStore {

    private $tryNumber;
    private $isNeedRerun;

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
                $table->integer('try_number');
                $table->string('url');
                $table->tinyInteger('status');
                $table->timestamps();
                $table->unique(['try_number', 'url']);
            });
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }


    public function getTableName() {
        return UrlsStateModel::$tableName;
    }

    public function getTryNumber() {
        if ($this->tryNumber > 0) {
            return $this->tryNumber;
        }

        $this->_initDb();
        $this->tryNumber = UrlsStateModel::max('try_number');
        if (!$this->isNeedRerun()) {
            $this->tryNumber++;
        }

        return $this->tryNumber;
    }

    /**
     *
     * @return boolean
     */
    public function isNeedRerun() {
        if (null !== $this->isNeedRerun) {
            return $this->isNeedRerun;
        }

        $this->_initDb();
        $this->isNeedRerun = false;
        $pendingCounts = UrlsStateModel::where([
            ['status', '=', UrlsStateModel::PENDING]
        ])->count();


        if ($pendingCounts > 0) {
            $this->isNeedRerun = true;
        }

        return $this->isNeedRerun;
    }

    public function saveStatus($url, $status) {
        $this->_initDb();

        $urlsStateModel = UrlsStateModel::firstOrNew(['try_number' => $this->getTryNumber(), 'url' => $url]);
        $urlsStateModel->try_number = $this->getTryNumber();
        $urlsStateModel->url = $url;
        $urlsStateModel->status = $status;
        return $urlsStateModel->save();
    }

    public function markPending($url) {
        return $this->saveStatus($url, UrlsStateModel::PENDING);
    }

    public function markProcess($url) {
        return $this->saveStatus($url, UrlsStateModel::PROCESS);
    }

    public function markRejected($url) {
        return $this->saveStatus($url, UrlsStateModel::REJECTED);
    }

    public function markCompleted($url) {
        return $this->saveStatus($url, UrlsStateModel::COMPLETED);
    }

    public function restoreStatusUrls($status) {
        $this->_initDb();
        return UrlsStateModel::where([
            ['try_number', '=', $this->getTryNumber()],
            ['status', $status]
        ])->pluck('url')->toArray();
    }

    /**
     *
     * @return string[]|null
     */
    public function restorePendingUrls() {
        return $this->restoreStatusUrls(UrlsStateModel::PENDING);
    }

    /**
     *
     * @return string[]|null
     */
    public function restoreCompletedUrls() {
        return $this->restoreStatusUrls(UrlsStateModel::COMPLETED);
    }

    /**
     *
     * @return string[]|null
     */
    public function restoreRejectedUrls() {
        return $this->restoreStatusUrls(UrlsStateModel::REJECTED);
    }

    /**
     *
     * @return string|null
     */
    public function restoreProcessedUrl() {
        return array_shift($this->restoreStatusUrls(UrlsStateModel::PROCESS));
    }

}
