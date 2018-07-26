<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\UrlCollector;
use Yugeon\Uniparser\UrlsStateStore;

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

    function testCanAddPendingUrls() {
        $url = 'http://example.com/asdf';
        $this->testClass->addPendingUrl($url);
        $this->assertArraySubset([$url], $this->testClass->getPendingUrls());
    }

    function testCanCheckIfUrlAlreadyExistInPendingList() {
        $url = 'http://example.com/asdf';
        $this->assertFalse($this->testClass->isPendingExist($url));
        $this->testClass->addPendingUrl($url);
        $this->assertTrue($this->testClass->isPendingExist($url));
    }

    function testNotAllowDuplicatePendingUrls() {
        $url = 'http://example.com/asdf';
        $this->assertCount(0, $this->testClass->getPendingUrls());
        $this->testClass->addPendingUrl($url);
        $this->testClass->addPendingUrl($url);
        $this->assertCount(1, $this->testClass->getPendingUrls());
    }

    function testCanAddRejectedUrls() {
        $rejectedUrl = 'http://example.com/rejected/asdf';
        $this->testClass->addRejectedUrl($rejectedUrl);
        $this->assertArraySubset([$rejectedUrl], $this->testClass->getRejectedUrls());
    }

    function testCanCheckIfUrlAlreadyExistInRejectedList() {
        $rejectedUrl = 'http://example.com/rejected/asdf';
        $this->assertFalse($this->testClass->isRejectedExist($rejectedUrl));
        $this->testClass->addRejectedUrl($rejectedUrl);
        $this->assertTrue($this->testClass->isRejectedExist($rejectedUrl));
    }

    function testNotAllowDuplicateRejectedUrls() {
        $rejectedUrl = 'http://example.com/rejected/asdf';
        $this->assertCount(0, $this->testClass->getRejectedUrls());
        $this->testClass->addRejectedUrl($rejectedUrl);
        $this->testClass->addRejectedUrl($rejectedUrl);
        $this->assertCount(1, $this->testClass->getRejectedUrls());
    }

    function testCanAddCompletedUrls() {
        $url = 'http://example.com/asdf';
        $this->testClass->addCompletedUrl($url);
        $this->assertArraySubset([$url], $this->testClass->getCompletedUrls());
    }

    function testCanCheckIfUrlAlreadyExistInCompletedList() {
        $url = 'http://example.com/asdf';
        $this->assertFalse($this->testClass->isCompletedExist($url));
        $this->testClass->addCompletedUrl($url);
        $this->assertTrue($this->testClass->isCompletedExist($url));
    }

    function testNotAllowDuplicateCompletedUrls() {
        $url = 'http://example.com/rejected/asdf';
        $this->assertCount(0, $this->testClass->getCompletedUrls());
        $this->testClass->addCompletedUrl($url);
        $this->testClass->addCompletedUrl($url);
        $this->assertCount(1, $this->testClass->getCompletedUrls());
    }

    function testDenyAddNotStringUrl() {
        $url = false;

        $this->testClass->addCompletedUrl($url);
        $this->testClass->addPendingUrl($url);
        $this->testClass->addRejectedUrl($url);

        $this->assertCount(0, $this->testClass->getCompletedUrls());
        $this->assertCount(0, $this->testClass->getPendingUrls());
        $this->assertCount(0, $this->testClass->getRejectedUrls());
    }

    function testCanCorrectDistributeUrlsByRejectedPendingAndCompletedUrls() {
        $rejectedUrl = 'http://example.com/1';
        $this->testClass->addRejectedUrl($rejectedUrl);

        $completedUrl = 'http://example.com/2';
        $this->testClass->addCompletedUrl($completedUrl);

        $pendingUrl = 'http://example.com/3';
        $this->testClass->addPendingUrl($pendingUrl);

        $newUrls = [
            'http://example.com/1',
            'http://example.com/2',
            'http://example.com/3',
            'http://example.com/4',
        ];

        $this->testClass->add($newUrls);

        $this->assertCount(1, $this->testClass->getRejectedUrls());
        $this->assertCount(1, $this->testClass->getCompletedUrls());
        $this->assertCount(2, $this->testClass->getPendingUrls());
    }

    function testUrlsNotInWithinLockedUrlsMustFallIntoRejected() {
        $lockUrl = '//example.com';
        $this->testClass->lockHost($lockUrl);

        $urls = [
            'http://example.com/',
            'http://example.com/1',
            'http://example.com/2/abs',
            'http://external.com/3',
        ];
        $this->testClass->add($urls);

        $this->assertCount(3, $this->testClass->getPendingUrls());
        $this->assertCount(1, $this->testClass->getRejectedUrls());
    }

    function testCanSetUrlMatcherCallback() {
        $urlMatcherCallback = function($url){
            return true;
        };
        $this->testClass->setUrlMatcherCallback($urlMatcherCallback);

        $this->assertEquals($urlMatcherCallback, $this->testClass->getUrlMatcherCallback());
    }

    function testCanApplyUrlMatcherCallbackBeforeAddUrl() {
        $isCalled = false;
        $url = 'http://example.com/abs';
        $urlMatcherCallback = function ($url) use (&$isCalled) {
            $isCalled = true;
            return true;
        };
        $this->testClass->setUrlMatcherCallback($urlMatcherCallback);
        $this->testClass->add($url);

        $this->assertTrue($isCalled);
    }

    function testMustFallIntoRejectedIfNotFitUrlMatcherCondition() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2/abs',
            'http://examle.com/3',
        ];

        $isMatcherCalled = false;
        $urlMatcherCallback = function($url) use(&$isMatcherCalled) {
            $condition = '/abs';
            $isMatcherCalled = true;
            $length = strlen($condition);
            return $length === 0 || (substr($url, -$length) === $condition);
        };

        $this->testClass->setUrlMatcherCallback($urlMatcherCallback);

        $this->testClass->add($urls);

        $needleUrl = 'http://examle.com/2/abs';

        $this->assertArraySubset([$needleUrl], $this->testClass->getPendingUrls());
        $this->assertCount(3, $this->testClass->getRejectedUrls());
        $this->assertEquals(1, $this->testClass->counts());
        $this->assertTrue($isMatcherCalled);
    }

    function testOnlyOneCurrentProcessedUrl() {
        $url = 'http://example.com/sdf';
        $this->testClass->setStartUrl($url);

        $this->assertFalse($this->testClass->getProcessedUrl());
        $this->testClass->next();
        $this->assertEquals($url, $this->testClass->getProcessedUrl());
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
        $this->testClass->lockHost($lockUrl);

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

    function testCanGetSetConfig() {
        $config = new Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass->setConfig($config->getConfig('UrlCollector'));
        $this->assertEquals($config->getConfig('UrlCollector.FollowLinks'),
                $this->testClass->getConfig()->getConfig('FollowLinks'));
    }

    function testCanCreateWithConfigInConstructor() {
        $config = ['aa' => 'bb'];
        $this->testClass = new Yugeon\Uniparser\UrlCollector(null, $config);
        $this->assertEquals($config['aa'], $this->testClass->getConfig()->getConfig('aa'));
    }

    function testTakeIntoSettingFollowLink() {
        $this->testClass->setConfig(['FollowLinks' => false]);

        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];
        $this->testClass->add($urls);

        $url = $this->testClass->next();
        $this->assertEquals($urls[0], $url);

        $actualUrl = $this->testClass->next();
        $this->assertNull($actualUrl);
    }

    function testClearProcessedUrlOnNextIteration() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);

        $processedUrl = $this->testClass->next();
        $this->assertEquals($urls[0], $processedUrl);

        $count = 0;
        while ($this->testClass->next()) {
            $count++;
        }

        $this->assertFalse($this->testClass->getProcessedUrl());
    }

    function testAutomaticallAddProcessedUrlToCompletedOnNextIteration() {
        $urls = [
            'http://examle.com/',
            'http://examle.com/1',
            'http://examle.com/2',
            'http://examle.com/3',
        ];

        $this->testClass->add($urls);

        $processedUrl1 = $this->testClass->next();
        $processedUrl2 = $this->testClass->next();
        
        $this->assertArraySubset([$processedUrl1], $this->testClass->getCompletedUrls());
    }

    function testCanRejectProcessingUrl() {
        $url = 'http://example.com/asdf1/1';
        $this->testClass->setProcessedUrl($url);
        $this->testClass->rejectProcessedUrl();
        $this->assertFalse($this->testClass->getProcessedUrl());
        $this->assertArraySubset([$url], $this->testClass->getRejectedUrls());
    }

    function testCanSetGetUrlsStateStore() {
        $urlsStateStore = $this->createMock(UrlsStateStore::class);
        $this->testClass->setConfig(['RerunOnFails' => false]);
        $this->testClass->setUrlsStateStore($urlsStateStore);
        $this->assertEquals($urlsStateStore, $this->testClass->getUrlsStateStore());
    }

    function testSaveUrlsStateIfStorePresented() {
        $urlsStateStore = $this->createMock(UrlsStateStore::class);
        $urlsStateStore->expects($this->once())->method('markPending');
        $urlsStateStore->expects($this->once())->method('markCompleted');
        $urlsStateStore->expects($this->once())->method('markProcess');
        $urlsStateStore->expects($this->once())->method('markRejected');
        $this->testClass->setConfig(['RerunOnFails' => false]);
        $this->testClass->setUrlsStateStore($urlsStateStore);

        $url = 'http://example.com/1';
        $this->testClass->addRejectedUrl($url);
        $this->testClass->addCompletedUrl($url);
        $this->testClass->addPendingUrl($url);
        $this->testClass->setProcessedUrl($url);
    }

    function testCanRestoreUrlsStateIfStorePresentedAndNeedRerunDetected() {
        $urlsStateStore = $this->createMock(UrlsStateStore::class);
        $urlsStateStore->expects($this->once())->method('isNeedRerun')->willReturn(true);
        $urlsStateStore->expects($this->once())->method('restorePendingUrls');
        $urlsStateStore->expects($this->once())->method('restoreCompletedUrls');
        $urlsStateStore->expects($this->once())->method('restoreProcessedUrl');
        $urlsStateStore->expects($this->once())->method('restoreRejectedUrls');
        $this->testClass->setConfig(['RerunOnFails' => true]);
        $this->testClass->setUrlsStateStore($urlsStateStore);
    }

    function testFailedUrlsMustBeRejected() {
        $urls = [
            '',
            'javascript:void(0)',
            '0',
            'ftp://adsfk.ru/',
            'file://adsfdf',
        ];

        $this->testClass->add($urls);
        $this->assertCount(0, $this->testClass->getPendingUrls());
    }

    function testDontAllowGetContentFromExternalUrls() {
        $baseUrl = 'http://example.com/';
        $this->testClass->lockHost($baseUrl);
        $externalUrl = 'http://google.com/';

        $this->testClass->add($externalUrl);

        $this->assertTrue(in_array($externalUrl, $this->testClass->getRejectedUrls()));
    }

    function testAllowGetContentFromLockedUrls() {
        $baseUrl = 'https://google.com';
        $this->testClass->lockHost($baseUrl);
        $externalUrl = 'https://google.com/adsf';
        $this->testClass->add($externalUrl);
        $this->assertArraySubset([$externalUrl], $this->testClass->getPendingUrls());
    }

}
