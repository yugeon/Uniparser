<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\Uniparser;

class UniparserTest extends TestCase {

    var $testClass;

    function setUp() {
        $config = __DIR__ . '/testConfig.yml';
        $this->testClass = new Uniparser($config);
        
        $urlCollector = $this->createMock(Yugeon\Uniparser\UrlCollector::class);
        $contentDelivery = $this->createMock(\Yugeon\Uniparser\ContentDelivery::class);
        $parser = $this->createMock(\Yugeon\Uniparser\Parser::class);
        $dataStore = $this->createMock(\Yugeon\Uniparser\DataStore::class);

        $this->testClass->init($urlCollector, $contentDelivery, $parser, $dataStore);
    }

    function testCanCreate() {
        $this->assertInstanceOf(Uniparser::class, $this->testClass);
        $this->assertNotNull($this->testClass);
    }

    function testCanUseStartUrlFromConfig() {
        $this->testClass->run();
    }

}