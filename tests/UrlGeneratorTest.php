<?php

namespace Yugeon\Uniparser\Tests;

use PHPUnit\Framework\TestCase;
use Yugeon\Uniparser\UrlGenerator;

class UrlGeneratorTest extends TestCase {

    private $testClass;

    function setUp() {
        $this->testClass = new UrlGenerator();
    }

    public function testClassCanBeInstantiated() {
        $this->assertTrue(is_object($this->testClass));
    }

    public function testObjectIsOfCorrectType() {
        $this->assertTrue(get_class($this->testClass) == 'Yugeon\Uniparser\UrlGenerator');
    }
}