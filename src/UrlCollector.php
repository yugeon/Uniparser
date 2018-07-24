<?php

namespace Yugeon\Uniparser;

use URL\Normalizer;
/**
 * Description of UrlLimiter
 *
 * @author yugeon
 */
class UrlCollector {
    private $startUrl;
    private $lockUrl;
    private $urls = [];
    private $processedUrls = [];
    private $unsuitableUrls = [];

    function __construct($urlNormalizer = null) {
        if ($urlNormalizer === null) {
            $urlNormalizer = new Normalizer();
        }

        $this->urlNormalizer = $urlNormalizer;
    }



    public function setStartUrl($url) {
        $this->startUrl = $url;
        return $this->add($url);
    }

    public function getStartUrl() {
        return $this->startUrl;
    }

    public function counts() {
        return count($this->urls);
    }

    public function add($urls) {
        if (is_array($urls)) {
            foreach ($urls as $url) {
                $this->add($url);
            }
        } else {
            $this->urlNormalizer->setUrl($urls);
            $urls = $this->urlNormalizer->normalize();

            if (!in_array($urls, $this->processedUrls) && !in_array($urls, $this->urls)) {
                $this->urls[] = $urls;
            }
        }

        return $this;
    }

    public function getUrls() {
        return $this->urls;
    }

    public function next(callable $matcherFn = null, ...$matcherArgs) {

        $url = array_shift($this->urls);
        if (null === $url) {
            return null;
        }

        if (!$this->checkUrlWithinLockUrl($url)) {
            $this->unsuitableUrls[] = $url;
            return $this->next($matcherFn, ...$matcherArgs);
        }

        if ($matcherFn && !$matcherFn($url, ...$matcherArgs)) {
            $this->unsuitableUrls[] = $url;
            return $this->next($matcherFn, ...$matcherArgs);
        }

        $this->processedUrls[] = $url;
        return $url;
    }

    public function lockUrl($lockUrl) {
        $lockUrlHost = parse_url($lockUrl, PHP_URL_HOST);
        $this->lockUrl = $lockUrlHost;
        return $this;
    }

    public function checkUrlWithinLockUrl($url) {
        if (!$this->lockUrl) {
            return true;
        }

        $urlHost = parse_url($url, PHP_URL_HOST);

        return $urlHost === $this->lockUrl;
    }

}
