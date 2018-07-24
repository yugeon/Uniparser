<?php

namespace Yugeon\Uniparser;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Description of Parser
 *
 * @author yugeon
 */
class Parser {

    private $content;
    private $crawler;
    private $charset;

    function __construct($crawler = null, $uri = null) {

        if (null === $crawler) {
            $crawler = new Crawler(null, $uri = null, $baseHref = null);
        }

        $this->crawler = $crawler;
    }

    public function setCrawler($crawler) {
        $this->crawler = $crawler;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        $this->crawler->clear();
        $this->crawler->addHtmlContent($this->content, $this->charset);
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    /**
     *
     * @return array of string urls
     */
    public function getAllUrls() {
        $urls = [];

        $links = $this->crawler->filter('a')->links();
        foreach ($links as $link) {
            $urls[] = $link->getUri();
        }

        return $urls;
    }

    public function filter($selector) {
        return $this->crawler->filter($selector);
    }

    public function filterXPath($selector) {
        return $this->crawler->filterXPath($selector);
    }

    public function filterText($selector) {
        $filtered = $this->filter($selector);
        if ($filtered->count() > 0) {
            return $filtered->first()->text();
        }
        return false;
    }

    public function filterAttr($selector, $attr) {
        $filtered = $this->filter($selector);
        if ($filtered->count() > 0) {
            return $filtered->first()->attr($attr);
        }
        return false;
    }

    public function isExist($selector) {
        $filtered = $this->filter($selector);
        if ($filtered->count() > 0) {
            return true;
        }

        return false;
    }

}
