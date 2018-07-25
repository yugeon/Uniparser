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

    public function testCanGetAllUrlsWithinSelector() {
        $this->setContent();

        $selector = 'div.block > a';
        $actualUrls = $this->testClass->getAllUrls($selector);

        $this->assertCount(2, $actualUrls);
    }

    public function testCanGetTextContentBySelector() {
        $this->setContent();

        $selector = 'div.description';
        $actualText = $this->testClass->filterText($selector);
        $this->assertEquals('bla bla description', $actualText);
    }

    public function testCanGetCollectionBySelector() {
        $this->setContent();

        $selector = 'div.sizes > .size *:first-child';
        $actualCollection = $this->testClass->filterCollection($selector);
        $this->assertTrue(is_array($actualCollection));
        $this->assertCount(3, $actualCollection);
    }

    public function testMustReturEmptyArrayIfCollectionEmpty() {
        $this->setContent();

        $selector = 'div.sizes_not_exist > .size *:first-child';
        $actualCollection = $this->testClass->filterCollection($selector);
        $this->assertTrue(is_array($actualCollection));
        $this->assertCount(0, $actualCollection);
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
        $this->assertEquals('new content', $this->testClass->getContent());
    }

    public function testCanApplyRegexpToUrl() {
        $url = 'https://www.example.com/ru/maio-tahity-97506.html';
        $pattern = '/(\d+)\.html$/i';
        $actual = $this->testClass->regexp($pattern, $url);
        $this->assertEquals('97506', $actual);
    }

    public function testMustReturnEmptyStringIfRegexpNotMatch() {
        $url = 'https://www.example.com/ru/maio-tahity-97506.html';
        $pattern = '/([a-b]+)\.html$/i';
        $actual = $this->testClass->regexp($pattern, $url);
        $this->assertEquals('', $actual);
    }

    public function testCanApplyRegexpToContent() {
        $this->setContent();

        $pattern = '/div\s+id="id(\d+)"/i';

        $actualText = $this->testClass->regexp($pattern, $this->testClass->getContent());
        $this->assertEquals('2344', $actualText);
    }

}
