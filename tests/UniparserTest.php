<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\Uniparser;

class UniparserTest extends TestCase {

    var $testClass;
    private $urlCollector;

    function setUp() {
        $config = __DIR__ . '/testConfig.yml';
        $this->testClass = new Uniparser($config);
        
        $this->urlCollector = $this->createMock(Yugeon\Uniparser\UrlCollector::class);
        $contentDelivery = $this->createMock(\Yugeon\Uniparser\ContentDelivery::class);
        $contentDelivery->method('getContent')->willReturn(file_get_contents(__DIR__ . '/test-page.html'));
        $parser = $this->createMock(\Yugeon\Uniparser\Parser::class);
        $dataStore = $this->createMock(\Yugeon\Uniparser\DataStore::class);
        $urlsStateStore = $this->createMock(\Yugeon\Uniparser\UrlsStateStore::class);
        $urlsStateStore->method('isNeedRerun')->willReturn(false);

        $this->testClass->setUrlsStateStore($urlsStateStore);
        $this->testClass->setUrlCollector($this->urlCollector);
        $this->testClass->setParser($parser);
        $this->testClass->setContentDelivery($contentDelivery);
        $this->testClass->setDataStore($dataStore);

        $this->testClass->init();
    }

    function testCanCreate() {
        $this->assertInstanceOf(Uniparser::class, $this->testClass);
        $this->assertNotNull($this->testClass);
    }

    function testCanUseStartUrlFromConfig() {
        $this->testClass->run();
    }

    function testCanSetCallbackForParsing() {
        $called = false;
        $url = 'http://example.com/a';

        $this->urlCollector->method('next')->willReturn('http://example.com/');
        $this->testClass->setUrlCollector($this->urlCollector);

        $this->testClass->setExtractCallback(function($url, $content, $parser) use (&$called) {
            $called = true;
        });
        
        $this->testClass->run($url);
        $this->assertTrue($called);
    }

    function testCanSetCallbackForUrlMatcher() {
        $called = false;
        $url = 'http://example.com/avs/23';
        $condition = '/avs';

        $this->urlCollector = new \Yugeon\Uniparser\UrlCollector();
        $this->testClass->setUrlCollector($this->urlCollector);

        $this->testClass->setUrlMatcherCallback(function ($url) use (&$called, $condition) {
            $called = true;

            // $condition at begin of url path
            $parsedUrl = parse_url($url);
            return strpos($parsedUrl['path'], $condition) === 0;

        });

        $this->urlCollector->add($url);
        $this->assertTrue($called);
    }

    function testCanSetGetUrlCollectorObject() {
        $urlCollector = $this->createMock(\Yugeon\Uniparser\UrlCollector::class);
        $this->testClass->setUrlCollector($urlCollector);
        $this->assertEquals($urlCollector, $this->testClass->getUrlCollector());
    }

    function testInitUrlCollectorOnce() {
        $urlCollector = $this->createMock(\Yugeon\Uniparser\UrlCollector::class);
        $this->testClass->setUrlCollector($urlCollector);
        $this->assertEquals($urlCollector, $this->testClass->getUrlCollector());
        $this->testClass->initUrlCollector();
        $this->assertEquals($urlCollector, $this->testClass->getUrlCollector());
    }

    function testCanSetGetParserObject() {
        $parser = $this->createMock(\Yugeon\Uniparser\Parser::class);
        $this->testClass->setParser($parser);
        $this->assertEquals($parser, $this->testClass->getParser());
    }

    function testInitUrlParserOnce() {
        $parser = $this->createMock(\Yugeon\Uniparser\Parser::class);
        $this->testClass->setParser($parser);
        $this->assertEquals($parser, $this->testClass->getParser());
        $this->testClass->initParser();
        $this->assertEquals($parser, $this->testClass->getParser());
    }

    function testCanSetGetContentDeliveryObject() {
        $contentDelivery = $this->createMock(\Yugeon\Uniparser\ContentDelivery::class);
        $this->testClass->setContentDelivery($contentDelivery);
        $this->assertEquals($contentDelivery, $this->testClass->getContentDelivery());
    }

    function testInitUrlContentDeliveryOnce() {
        $contentDelivery = $this->createMock(\Yugeon\Uniparser\ContentDelivery::class);
        $this->testClass->setContentDelivery($contentDelivery);
        $this->assertEquals($contentDelivery, $this->testClass->getContentDelivery());
        $this->testClass->initContentDelivery();
        $this->assertEquals($contentDelivery, $this->testClass->getContentDelivery());
    }

    function testCanSetGetDataStoreObject() {
        $dataStore = $this->createMock(\Yugeon\Uniparser\DataStore::class);
        $this->testClass->setDataStore($dataStore);
        $this->assertEquals($dataStore, $this->testClass->getDataStore());
    }

    function testInitUrlDataStoreOnce() {
        $dataStore = $this->createMock(\Yugeon\Uniparser\DataStore::class);
        $this->testClass->setDataStore($dataStore);
        $this->assertEquals($dataStore, $this->testClass->getDataStore());
        $this->testClass->initDataStore();
        $this->assertEquals($dataStore, $this->testClass->getDataStore());
    }
}