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
        $parser = $this->createMock(\Yugeon\Uniparser\Parser::class);
        $dataStore = $this->createMock(\Yugeon\Uniparser\DataStore::class);

        $this->testClass->init($this->urlCollector, $contentDelivery, $parser, $dataStore);
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

        $this->urlCollector->method('next')->willReturn('http://example.com');
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
        $this->urlCollector->setStartUrl($url);
        $this->testClass->setUrlCollector($this->urlCollector);

        $this->testClass->setUrlMatcherCallback(function ($url) use (&$called, $condition) {
            $called = true;

            // $condition at begin of url path
            $parsedUrl = parse_url($url);
            return strpos($parsedUrl['path'], $condition) === 0;

        });

        $this->testClass->run($url);
        $this->assertTrue($called);
    }
}