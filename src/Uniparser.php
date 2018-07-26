<?php

namespace Yugeon\Uniparser;

class Uniparser {
    private $config;
    /**
     *
     * @var UrlCollector
     */
    private $urlCollector;
    private $contentDelivery;
    /**
     *
     * @var Parser
     */
    private $parser;
    private $dataStore;
    private $parsingCallback;
    private $urlsStateStore;

    public function __construct($configPath) {
        if (!$configPath) {
            throw new Exception('No initial config path');
        }

        $this->config = new Config($configPath);
    }

    function init() {

        if (!$this->config->isValid()) {
            throw new Exception('Config not valid');
        }

        // TODO: url generator
        $this->initUrlCollector();
        $this->initContentDelivery();
        $this->initParser();
        $this->initDataStore();
    }

    function setUrlCollector($urlCollector) {
        $this->urlCollector = $urlCollector;
        return $this;
    }

    public function getUrlCollector() {
        return $this->urlCollector;
    }

    public function initUrlCollector($urlCollector = null) {
        if (!$this->urlCollector) {
            $this->urlCollector = $urlCollector ?:
                new UrlCollector(null, $this->config->getConfig('UrlCollector'));
        }

        // store urls state for rerun
        if ($this->config->getConfig('UrlCollector.SaveState')) {
            $urlsStateStore = $this->urlsStateStore ?:
                    new UrlsStateStore($this->config->getConfig('DataStore'));
            $this->urlCollector->setUrlsStateStore($urlsStateStore);
        }
    }

    function getParser() {
        return $this->parser;
    }

    function setParser($parser) {
        $this->parser = $parser;
        return $this;
    }

    function initParser($parser = null) {
        if (!$this->parser) {
            $this->parser = $parser ?: new Parser();
        }
    }

    function getContentDelivery() {
        return $this->contentDelivery;
    }

    function setContentDelivery($contentDelivery) {
        $this->contentDelivery = $contentDelivery;
        return $this;
    }

    function initContentDelivery($contentDelivery = null) {
        if (!$this->contentDelivery) {
            $this->contentDelivery = $contentDelivery ?:
                new ContentDelivery($this->config->getConfig('ContentDelivery'));
        }
    }

    function setUrlsStateStore($urlsStateStore) {
        $this->urlsStateStore = $urlsStateStore;
        return $this;
    }

    function getDataStore() {
        return $this->dataStore;
    }

    function setDataStore($dataStore) {
        $this->dataStore = $dataStore;
        return $this;
    }

    function initDataStore($dataStore = null) {
        if (!$this->dataStore) {
            $this->dataStore = $dataStore ?: new DataStore($this->config->getConfig('DataStore'));
        }
    }


    public function run($startUrl = '') {
        $this->init();

        if (!$startUrl) {
            $startUrl = $this->config->getConfig('General.StartUrl');

            if (!$startUrl) {
                throw new Exception('No initial url');
            }
        }

        $this->urlCollector->setStartUrl($startUrl);

        $limit = $this->config->getConfig('General.LimitUrls', 0);
        $limit = $limit > 0 ? $limit : PHP_INT_MAX;
        $counter = 0;

        while ($counter < $limit && ($url = $this->urlCollector->next())) {
            $counter++;

            try {
                $content = $this->contentDelivery->getContent($url);
                $this->parser->setContent($content);
                $urls = $this->parser->getAllUrls();
                $this->urlCollector->add($urls);

                $data = false;
                if (is_callable($this->parsingCallback)) {
                    $data = call_user_func($this->parsingCallback, $url, $content, $this->parser);
                }

                if ($data) {
                    $this->dataStore->save($url, $data);
                } else {
                    $this->urlCollector->rejectProcessedUrl();
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }

    }

    public function setExtractCallback(callable $callback) {
        $this->parsingCallback = $callback;
        return $this;
    }

    public function setUrlMatcherCallback(callable $callback) {
        $this->urlCollector->setUrlMatcherCallback($callback);
        return $this;
    }

}