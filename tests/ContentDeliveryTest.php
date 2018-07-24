<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\ContentDelivery;
use \Curl\Curl;

class ContentDeliveryTest extends TestCase {


    private $testClass;
    private $testContent;
    private $curl;

    function setUp() {
        $this->testContent = 'a;sdlfkaj;sdf';
        $this->curl = $this->createMock(Curl::class);
        $this->curl->method('get');
        $this->curl->error = false;
        $this->curl->response = $this->testContent;
        $this->curl->responseHeaders = [
            'Content-Type' => 'text/html'
        ];

        $this->testClass = new ContentDelivery('', $this->curl);
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\ContentDelivery');
    }

    function testCanSetGetConfig() {
        $config = new \Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass->setConfig($config->getConfig('ContentDelivery'));
        $actualConfig = $this->testClass->getConfig();
        $this->assertEquals($config->getConfig('ContentDelivery.aa'), $actualConfig->getConfig('aa'));
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfNoConfig() {
        $this->testClass->getConfig();
    }

    function testCanSetConfigInConstructor() {
        $config = new \Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass = new ContentDelivery($config->getConfig('ContentDelivery'), $this->curl);
        $actualConfig = $this->testClass->getConfig();
        $this->assertEquals($config->getConfig('ContentDelivery.aa'), $actualConfig->getConfig('aa'));
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfConfigNotConfigType() {
        $config = 'asdfasf';
        $this->testClass = new ContentDelivery($config, $this->curl);
    }

    function testDontAllowGetContentFromExternalUrls() {
        $baseUrl = 'http://example.com';
        $this->testClass->lockHost($baseUrl);
        $externalUrl = 'http://google.com';

        $actualContent = $this->testClass->getContent($externalUrl);
        $this->assertFalse($actualContent);
    }

    function testAllowGetContentFromLockedUrls() {
        $baseUrl = 'https://google.com';
        $this->testClass->lockHost($baseUrl);
        $externalUrl = 'https://google.com/adsf';

        $actualContent = $this->testClass->getContent($externalUrl);

        $this->assertGreaterThan(0, strlen($actualContent));
        $this->assertEquals($this->testContent, $actualContent);
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfCannotGetContent() {
        $baseUrl = 'https://google.com';
        $this->testClass->lockHost($baseUrl);
        $externalUrl = 'https://google.com/adsf';

        $this->curl->error = true;
        $this->curl->errorCode = 500;
        $this->curl->errorMessage = 'Internal error';

        $actualContent = $this->testClass->getContent($externalUrl);
    }

    // TODO: custom user agent

    function testTargetMustBeAllowedMimeTypes() {
        $contentTypes = ['text/html'];
        $this->testClass->setContentTypes($contentTypes);

        $baseUrl = 'https://google.com';
        $this->testClass->lockHost($baseUrl);

        $actualContent = $this->testClass->getContent($baseUrl);
        $this->assertEquals($this->testContent, $actualContent);
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfNotAllowedContentType() {
        $baseUrl = 'https://google.com';
        $this->testClass->lockHost($baseUrl);

        $this->curl->responseHeaders = [
            'Content-Type' => 'text/javascript'
        ];

        $config = new \Yugeon\Uniparser\Config(__DIR__ . '/testConfig.yml');
        $this->testClass->setConfig($config->getConfig('ContentDelivery'));

        $this->testClass->getContent($baseUrl);

    }
}
