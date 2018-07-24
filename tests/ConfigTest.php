<?php

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\Config;

class ParserTest extends TestCase {

    private $testClass;

    function setUp() {
        $this->testClass = new Config();
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\Config');
    }

    function testCanGetSetConfigPath() {
        $configPath = __DIR__ . '/testConfig.yml';

        $this->testClass->setConfigPath($configPath);
        $this->assertEquals($configPath, $this->testClass->getConfigPath());
    }

    function testCanSetPathToConfigThrowConstructor() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass = new Config($configPath);

        $this->assertEquals($configPath, $this->testClass->getConfigPath());
    }

    function testCanLoadYamlConfigFromConfigPath() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass = new Config($configPath);
        $this->testClass->load();
        $this->assertNotFalse($this->testClass->getConfig());
    }

    function testCanLoadYamlFromDirectPath() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);
        $this->assertNotFalse($this->testClass->getConfig());
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfIncorrectConfigPath() {
        $configPath = __DIR__ . '/testConfig_notExist.yml';
        $this->testClass->load($configPath);
    }

    /**
     * @expectedException Exception
     */
    function testMustThrowExceptionIfInvalidConfig() {
        $configPath = __DIR__ . '/test-page.html';
        $this->testClass->load($configPath);
    }

    function testCanGetConfigLoadStatus() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);
        $this->assertTrue($this->testClass->isValid());
    }

    function testDefaultConfigIsNotValid() {
        $this->assertFalse($this->testClass->isValid());
    }

    function testConfigHasSectionUrlGenerators() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);
        $this->assertArrayHasKey('UrlGenerators', $this->testClass->getConfig());
    }

    function testCanReturnTopSectionByName() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);

        $this->assertNotFalse($this->testClass->getConfig('UrlGenerators'));
    }

    function testCanReturnArbitarySectionByName() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);

        $section = 'UrlGenerators.testField2';
        $this->assertEquals('testValue2', $this->testClass->getConfig($section));
    }

    function testCanReturnEmptyIfSectionNotExistAndNoDefaultValue() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);

        $section = 'UrlGenerators.testField_notExist';

        $this->assertEquals('', $this->testClass->getConfig($section));
    }

    function testCanReturnDefaultValueIfKeyNotExist() {
        $configPath = __DIR__ . '/testConfig.yml';
        $this->testClass->load($configPath);

        $default = 'defaultValue';
        $section = 'UrlGenerators.testField_notExist';

        $this->assertEquals($default, $this->testClass->getConfig($section, $default));
    }

}
