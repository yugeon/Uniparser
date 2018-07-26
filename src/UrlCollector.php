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
    private $lockHost;

    private $pendingUrls = [];
    private $processedUrl = false;
    private $completedUrls = [];
    private $rejectedUrls = [];

    private $config;
    private $isNextAlreadyCall = false;
    private $urlMatcherCallback;

    /**
     *
     * @var UrlsStateStore
     */
    private $urlsStateStore;
    private $lockedHost;

    function __construct($urlNormalizer = null, $config = null) {
        if ($urlNormalizer === null) {
            $urlNormalizer = new Normalizer();
        }

        if ($config) {
            $this->setConfig($config);
        }

        $this->urlNormalizer = $urlNormalizer;
    }

    function getUrlsStateStore() {
        return $this->urlsStateStore;
    }

    function setUrlsStateStore(UrlsStateStore $urlsStateStore) {
        $this->urlsStateStore = $urlsStateStore;

        $configRerun = $this->config->getConfig('RerunOnFails');
        if ($this->urlsStateStore && $configRerun) {
            $isNeedRerun = $this->urlsStateStore->isNeedRerun();
            if ($isNeedRerun) {
                $this->pendingUrls = $this->urlsStateStore->restorePendingUrls();
                $this->rejectedUrls = $this->urlsStateStore->restoreRejectedUrls();
                $this->completedUrls = $this->urlsStateStore->restoreCompletedUrls();
                $this->processedUrl = $this->urlsStateStore->restoreProcessedUrl();
            }
        }

        return $this;
    }

    public function setStartUrl($url) {
        $this->startUrl = $url;

        $lockHost = $this->getConfig()->getConfig('LockHost');
        if ($lockHost) {
            $this->lockHost($url);
        }

        return $this->add($url);
    }

    public function getStartUrl() {
        return $this->startUrl;
    }

    public function counts() {
        return count($this->pendingUrls);
    }

    private function isAllowedUrl($url, $allowed_url_schemes = ['http', 'https']) {
        $valid_url = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) !== false;
        if ($valid_url) {
            $parsedUrl = parse_url($url);

            if ($this->lockedHost && $parsedUrl['host'] !== $this->lockedHost) {
                return false;
            }

            return in_array($parsedUrl['scheme'], $allowed_url_schemes, true);
        }
        $valid_ip = filter_var($url, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        return $valid_ip;
    }

    /**
     *
     * @param string|string[] $url
     * @return $this
     */
    public function add($url) {
        if (is_array($url)) {
            foreach ($url as $url) {
                $this->add($url);
            }
        } else {
            $this->urlNormalizer->setUrl($url);
            $url = $this->urlNormalizer->normalize();

            if (!$this->isAllowedUrl($url)) {
                $this->addRejectedUrl($url);
                return $this;
            }

            // check if it alreadey in rejected
            if ($this->isRejectedExist($url)) {
                return $this;
            }

            // check if it alreadey in completed
            if ($this->isCompletedExist($url)) {
                return $this;
            }

            // check if it alreadey in pending
            if ($this->isPendingExist($url)) {
                return $this;
            }

            if (!$this->isUrlWithinLockUrl($url)) {
                $this->addRejectedUrl($url);
                return $this;
            }

            if (is_callable($this->urlMatcherCallback) && !call_user_func($this->urlMatcherCallback, $url)) {
                $this->addRejectedUrl($url);
                return $this;
            }

            $this->addPendingUrl($url);
        }

        return $this;
    }

    public function getUrls() {
        return $this->pendingUrls;
    }

    public function next() {
        $this->setProcessedUrl(false);

        $isFollowLinks = $this->getConfig()->getConfig('FollowLinks', true);
        if (!$isFollowLinks && $this->isNextAlreadyCall) {
            return null;
        }

        $this->isNextAlreadyCall = true;

        $url = array_shift($this->pendingUrls);
        if (null === $url) {
            return null;
        }

        $this->setProcessedUrl($url);
        return $url;
    }

    public function lockHost($lockUrl) {
        $lockUrlHost = parse_url($lockUrl, PHP_URL_HOST);
        $this->lockHost = $lockUrlHost;
        return $this;
    }

    /**
     *
     * @param string $url
     * @return boolean True if url within Lock url
     */
    public function isUrlWithinLockUrl($url) {
        if (!$this->lockHost) {
            return true;
        }

        $urlHost = parse_url($url, PHP_URL_HOST);

        return $urlHost === $this->lockHost;
    }

    /**
     * 
     * @param Config $config
     */
    public function setConfig($config) {
        if (!($config instanceof Config)) {
            if (is_array($config)) {
                $configObj = new Config();
                $config = $configObj->setConfig($config);
            } else {
                throw new \Exception('Config not instance of Yugeon\Uniparser\Config');
            }
        }
        $this->config = $config;

        return $this;
    }

    function getConfig() {
        if (!$this->config) {
            $this->setConfig([]);
        }

        return $this->config;
    }

    /**
     * 
     * @param string $url Normalized url
     * @return boolean True if successfull add url
     */
    public function addRejectedUrl($url) {
        if ($url && !$this->isRejectedExist($url)) {
            $this->rejectedUrls[] = $url;
            if ($this->urlsStateStore) {
                $this->urlsStateStore->markRejected($url);
            }
            return true;
        }

        return false;
    }

    /**
     *
     * @return string[] Array of rejected urls
     */
    public function getRejectedUrls() {
        return $this->rejectedUrls;
    }

    /**
     *
     * @param string $url
     * @return boolean True if exist, false - not exist
     */
    public function isRejectedExist($url) {
        return in_array($url, $this->rejectedUrls);
    }

    /**
     *
     * @param string $url
     * @return boolean
     */
    public function addPendingUrl($url) {
        if ($url && !$this->isPendingExist($url)) {
            $this->pendingUrls[] = $url;
            if ($this->urlsStateStore) {
                $this->urlsStateStore->markPending($url);
            }
            return true;
        }

        return false;
    }

    /**
     *
     * @return string[] Array of pending urls
     */
    public function getPendingUrls() {
        return $this->pendingUrls;
    }

    /**
     *
     * @param string $url
     * @return boolean True if exist, false - not exist
     */
    public function isPendingExist($url) {
        return in_array($url, $this->pendingUrls);
    }

    /**
     *
     * @param string $url
     * @return boolean
     */
    public function addCompletedUrl($url) {
        if ($url && !$this->isCompletedExist($url)) {
            $this->completedUrls[] = $url;
            if ($this->urlsStateStore) {
                $this->urlsStateStore->markCompleted($url);
            }
            return true;
        }

        return false;
    }

    /**
     *
     * @return string[] Array of completed urls
     */
    public function getCompletedUrls() {
        return $this->completedUrls;
    }

    /**
     *
     * @param string $url
     * @return boolean True if exist, false - not exist
     */
    public function isCompletedExist($url) {
        return in_array($url, $this->completedUrls);
    }

    public function setUrlMatcherCallback(callable $urlMatcherCallback) {
        $this->urlMatcherCallback = $urlMatcherCallback;
        return $this;
    }

    public function getUrlMatcherCallback() {
        return $this->urlMatcherCallback;
    }

    /**
     *
     * @return string|false
     */
    public function getProcessedUrl() {
        return $this->processedUrl;
    }

    public function setProcessedUrl($url) {
        if (false === $url) {
            $this->addCompletedUrl($this->processedUrl);
        }

        $this->processedUrl = $url;
        if ($this->urlsStateStore && $url) {
            $this->urlsStateStore->markProcess($url);
        }
        return $this;
    }

    public function rejectProcessedUrl() {
        $this->addRejectedUrl($this->getProcessedUrl());
        $this->processedUrl = false;
        return $this;
    }

}
