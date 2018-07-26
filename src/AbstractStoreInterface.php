<?php

namespace Yugeon\Uniparser;

/**
 *
 * @author yugeon
 */
interface AbstractStoreInterface {
    public function getTableName();
    public function createSchema();
}
