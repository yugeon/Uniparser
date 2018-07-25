<?php

//use PHPUnit\Framework\TestCase;
//use PHPUnit\Framework\Extensions\Database\TestCase;
use Yugeon\Uniparser\DataStore;

class DataStoreTest extends \PHPUnit_Extensions_Database_TestCase {

    private $testClass;

    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;
    private $conn;

    function setUp() {
        $this->testClass = new DataStore();
        $config = new \Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass->setConnConfig($config->getConfig('DataStore.database'));

        $this->testClass->_initDb();
        $this->testClass->createSchema();
        self::$pdo = $this->testClass->getPdoForTest();
    }

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO('sqlite::memory:');
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }

        return $this->conn;
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        $tableName = $this->testClass->getTableName();
        return new \PHPUnit_Extensions_Database_DataSet_ArrayDataSet([
            $tableName => [],
        ]);
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\DataStore');
    }

    public function testMustCheckIfSchemaAlreadyExist() {
        $this->testClass->_initDb();
        $this->testClass->createSchema();
    }

    public function testCanStoreArbitaryData() {
        $arbitaryData = [
            'sku' => '123123',
            'name' => 'DKjfkfk kjdkfdj',
            'price' => 23.12,
        ];
        $url = 'http://example.com/id/122';

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->save($url, $arbitaryData);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));
    }

    public function testCanReplaceExistenDataByUrl() {
        $arbitaryData = [
            'sku' => '123123',
            'name' => 'DKjfkfk kjdkfdj',
            'price' => 23.12,
        ];
        $url = 'http://example.com/id/122';
        $tableName = $this->testClass->getTableName();

        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->save($url, $arbitaryData);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));

        $this->testClass->save($url, $arbitaryData);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));
    }
}