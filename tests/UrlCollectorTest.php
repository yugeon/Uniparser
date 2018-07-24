<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\UrlCollector;

class UrlCollectorTest extends TestCase {

    var $testClass;

    function setUp() {
        $this->testClass = new UrlCollector();
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\UrlCollector');
    }

    function testCanAcceptStartUrl() {
        $startUrl = 'https://example.com';
        $this->testClass->setStartUrl($startUrl);
        $this->assertEquals($startUrl, $this->testClass->getStartUrl());
    }

    function testCanAccamulateUrls() {
        $urls = [
            'http://examle.com',
            'http://examle.com/0',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];
        $this->testClass->setStartUrl($urls[0]);
        $this->assertEquals(1, $this->testClass->counts());
        $this->testClass->add($urls[1]);
        $this->assertEquals(2, $this->testClass->counts());
        $this->testClass->add($urls[2]);
        $this->testClass->add($urls[3]);
        $this->testClass->add($urls[4]);
        $this->assertEquals(5, $this->testClass->counts());
    }

    function testCanAccamulateArrayOfUrls() {
        $urls = [
            'http://examle.com',
            'http://examle.com/0',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);
        $this->assertEquals(5, $this->testClass->counts());
    }

    function testNotAllowDublicateValues() {
        $urls = [
            'http://examle.com',
            'http://examle.com/0',
            'http://examle.com/1',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);
        $this->testClass->add($urls[0]);
        $this->assertEquals(5, $this->testClass->counts());
    }

    function testNotAllowDublicateValuesCaseInsensetive() {
        $urls = [
            'http://exAmle.com',
            'http://examle.com/0',
            'http://examle.Com/1',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examlE.com/3',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);
        $this->testClass->add($urls[0]);
        $this->assertEquals(5, $this->testClass->counts());
    }

    function testNotAllowDublicateUrls() {
        $urls = [
            'http://examle.com',
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/1/',
            'http://examle.com/2',
            'http://examle.com/3',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);
        $this->testClass->add($urls[0]);
        $this->assertEquals(5, $this->testClass->counts());
    }

//    function testCanMarkUrlAsCompleted() {
//        $urls = [
//            'http://examle.com/',
//            'http://examle.com/1',
//            'http://examle.com/2',
//            'http://examle.com/3',
//        ];
//    }

    function testCanReturnNextUrl() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];
        $this->testClass->add($urls);
        
        $url0 = $this->testClass->next();
        $this->assertEquals($urls[0], $url0);

        $url1 = $this->testClass->next();
        $this->assertEquals($urls[1], $url1);
    }

    function testNextMustRemoveUrlFromListOfUrls() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];
        $this->testClass->add($urls);

        $url0 = $this->testClass->next();
        $this->assertEquals(3, $this->testClass->counts());
    }

    function testNotAllowDuplicateProcessedUrls() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];

        $newUrls = [
            'http://examle.com/',
            'http://examle.com/4',
        ];

        $this->testClass->add($urls);

        $url0 = $this->testClass->next();
        $this->testClass->add($url0);
        $this->assertEquals(3, $this->testClass->counts());

        $this->testClass->add($newUrls);
        $this->assertEquals(4, $this->testClass->counts());
    }

    function testTheNextMethodMustReturnOnlySatisfyTheCondition() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2/abs',
            'http://examle.com/3',
        ];
        $this->testClass->add($urls);
        $matcherFn = function($url, $condition) {
            $length = strlen($condition);
            return $length === 0 || (substr($url, -$length) === $condition);
        };

        $needleUrl = 'http://examle.com/2/abs';
        $matcherArgs = '2/abs';
        $actualUrl = $this->testClass->next($matcherFn, $matcherArgs);
        $this->assertEquals($needleUrl, $actualUrl);
        $this->assertEquals(1, $this->testClass->counts());
    }

    function testCanIterateAcrossAllUrls() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2/abs',
            'http://examle.com/3',
        ];
        $this->testClass->add($urls);

        $urlCounter = 0;
        while($this->testClass->next()){
            $urlCounter++;
        }

        $this->assertEquals(4, $urlCounter);
        $this->assertEquals(0, $this->testClass->counts());
    }

    function testCanIterateOnlyWithinLockUrl() {
        $lockUrl = '//examle.com';
        $this->testClass->lockUrl($lockUrl);

        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2/abs',
            'http://external.com/3',
        ];
        $this->testClass->add($urls);

        $urlCounter = 0;
        while($this->testClass->next()){
            $urlCounter++;
        }

        $this->assertEquals(3, $urlCounter);
        $this->assertEquals(0, $this->testClass->counts());
    }

}
