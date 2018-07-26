<?php 

use Yugeon\Uniparser\UrlsStateStore;
use Yugeon\Uniparser\DataStore\UrlsStateModel;

class UrlStateStoreTest extends \PHPUnit_Extensions_Database_TestCase {

    private $testClass;

    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;
    private $conn;

    function setUp() {
        $this->testClass = new UrlsStateStore();
        $config = new \Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass->setConnConfig($config->getConfig('DataStore.database'));

        $this->testClass->_initDb($force = true);
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
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\UrlsStateStore');
    }

    public function testMustCheckIfSchemaAlreadyExist() {
        $this->testClass->_initDb();
        $this->testClass->createSchema();
    }

    function testCanGetTryNumberForEmpyBase() {
        $tryNumber = $this->testClass->getTryNumber();
        $this->assertEquals(1, $tryNumber);
    }

    public function testCanMarkUrlAsPending() {
        $url = 'http://example.com/12323/333';
        
        $tableName = $this->testClass->getTableName();
        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->markPending($url);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));

        $pdo = $this->getConnection()->getConnection();
        $q = $pdo->query('SELECT * FROM ' . $tableName);
        $data = $q->fetch();
        $this->assertArraySubset(['try_number' => 1, 'url' => $url, 'status' => UrlsStateModel::PENDING], $data);
    }

    public function testCanMarkUrlAsProcessing() {
        $url = 'http://example.com/12323/333';

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->markProcess($url);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));

        $pdo = $this->getConnection()->getConnection();
        $q = $pdo->query('SELECT * FROM ' . $tableName);
        $data = $q->fetch();
        $this->assertArraySubset(['try_number' => 1, 'url' => $url, 'status' => UrlsStateModel::PROCESS], $data);
    }

    public function testCanMarkUrlAsRejected() {
        $url = 'http://example.com/12323/333';

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->markRejected($url);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));

        $pdo = $this->getConnection()->getConnection();
        $q = $pdo->query('SELECT * FROM ' . $tableName);
        $data = $q->fetch();
        $this->assertArraySubset(['try_number' => 1, 'url' => $url, 'status' => UrlsStateModel::REJECTED], $data);
    }
    public function testCanMarkUrlAsCompleted() {
        $url = 'http://example.com/12323/333';

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(0, $this->getConnection()->getRowCount($tableName));
        $this->testClass->markCompleted($url);
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));

        $pdo = $this->getConnection()->getConnection();
        $q = $pdo->query('SELECT * FROM ' . $tableName);
        $data = $q->fetch();
        $this->assertArraySubset(['try_number' => 1, 'url' => $url, 'status' => UrlsStateModel::COMPLETED], $data);
    }

    function testCanDetectNewTryIfBaseEmpty() {
        $isNeedRerun = $this->testClass->isNeedRerun();
        $this->assertFalse($isNeedRerun);
    }

    function testCanDetectNewTryIfPreviousAttemptWasSuccessful() {
        $url = 'http://example.com';
        $this->testClass->markCompleted($url);

        $this->testClass = new UrlsStateStore();
        $isNeedRerun = $this->testClass->isNeedRerun();

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));
        $this->assertFalse($isNeedRerun);
    }

    function testCanDetectRerunIfPreviosuAttemptNotCompleted() {
        $url = 'http://example.com';
        $this->testClass->markPending($url);

        $this->testClass = new UrlsStateStore();
        $isNeedRerun = $this->testClass->isNeedRerun();

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));
        $this->assertTrue($isNeedRerun);
    }

    function testCanDetectNewTryIfPendingInCurrentTry() {
        $url = 'http://example.com';
        $this->testClass->markPending($url);
        $isNeedRerun = $this->testClass->isNeedRerun();

        $tableName = $this->testClass->getTableName();
        $this->assertEquals(1, $this->getConnection()->getRowCount($tableName));
        $this->assertFalse($isNeedRerun);
    }

    function testCanRestorePendingUrls() {
        $urls = [
            'http://example.com/1',
            'http://example.com/2',
            'http://example.com/3',
        ];
        foreach ($urls as $url) {
            $this->testClass->markPending($url);
        }

        $this->testClass = new UrlsStateStore();
        $actualUrls = $this->testClass->restorePendingUrls();
        $this->assertEquals($urls, $actualUrls);
    }

    function testCanRestoreCompletedUrls() {
        $urls = [
            'http://example.com/1',
            'http://example.com/2',
            'http://example.com/3',
        ];
        foreach ($urls as $url) {
            $this->testClass->markCompleted($url);
        }
        $this->testClass->markPending('http://example.com/4');

        $this->testClass = new UrlsStateStore();
        $actualUrls = $this->testClass->restoreCompletedUrls();
        $this->assertEquals($urls, $actualUrls);
    }

    function testCanRestoreRejectedUrls() {
        $urls = [
            'http://example.com/1',
            'http://example.com/2',
            'http://example.com/3',
        ];
        foreach ($urls as $url) {
            $this->testClass->markRejected($url);
        }
        $this->testClass->markPending('http://example.com/4');

        $this->testClass = new UrlsStateStore();
        $actualUrls = $this->testClass->restoreRejectedUrls();
        $this->assertEquals($urls, $actualUrls);
    }

    function testCanRestoreProcessUrls() {
        $urls = [
            'http://example.com/1',
            'http://example.com/2',
            'http://example.com/3',
        ];
        $this->testClass->markProcess($urls[0]);

        $this->testClass->markPending('http://example.com/4');

        $this->testClass = new UrlsStateStore();
        $actualUrl = $this->testClass->restoreProcessedUrl();
        $this->assertEquals($urls[0], $actualUrl);
    }

}