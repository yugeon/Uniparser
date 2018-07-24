<?php

namespace Yugeon\Uniparser\Tests;

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\Parser;
use Symfony\Component\DomCrawler\Crawler;

class ParserTest extends TestCase {

    private $testClass;
    static private $testContent;

    static function setUpBeforeClass() {
        self::$testContent = file_get_contents(__DIR__ . '/test-page.html');
    }

    function setUp() {
        $this->testClass = new Parser();
    }

    function setContent() {
        $uri = 'http://example.com';
        $crawler = new Crawler(null, $uri);
        $this->testClass->setCrawler($crawler);
        $this->testClass->setContent(self::$testContent);
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\Parser');
    }

    public function testCanGetSetContent() {
        $content = 'asldfkasldf';
        $this->testClass->setContent($content);
        $this->assertEquals($content, $this->testClass->getContent());
    }

    /**
     * @expectedException Exception
     */
    public function testMustBePresentBaseUrl() {
        $this->testClass->setContent(self::$testContent);
        $this->testClass->getAllUrls();
    }

    public function testCanGetAllUrls() {
        $this->setContent();

        $actualUrls = $this->testClass->getAllUrls();

        $this->assertCount(5, $actualUrls);
    }

    public function testCanGetTextContentBySelector() {
        $this->setContent();

        $selector = 'div.description';
        $actualText = $this->testClass->filterText($selector);
        $this->assertEquals('bla bla description', $actualText);
    }

    public function testCanGetAttrValueBySelector() {
        $this->setContent();

        $selector = 'div#id2344';
        $attr = 'id';
        $actualValue = $this->testClass->filterAttr($selector, $attr);
        $this->assertEquals('id2344', $actualValue);
    }

    public function testCanCheckIfIssetItemBySelector() {
        $this->setContent();

        $actualValue = $this->testClass->isExist('div#id2344');
        $this->assertTrue($actualValue);

        $actualValue = $this->testClass->isExist('div#id2344_not_exist');
        $this->assertFalse($actualValue);
    }

    public function testMustClearCrawlerBeforeSetContent() {
        $this->setContent();
        $this->testClass->setContent('new content');
    }
}
