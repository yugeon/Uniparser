<?php

namespace Yugeon\Uniparser;

class Uniparser {
    private $config;
    private $urlCollector;
    private $contentDelivery;
    private $parser;
    private $dataStore;
    private $parsingCallback;
    private $urlMatcherCallback;

    public function __construct($configPath) {
        if (!$configPath) {
            throw new Exception('No initial config path');
        }

        $this->config = new Config($configPath);
        $this->init();
    }

    function init($urlCollector = null, $contentDelivery = null, $parser = null, $dataStore = null) {
        if (!$this->config->isValid()) {
            throw new Exception('Config not valid');
        }

        // TODO: url generator
        $this->urlCollector = $urlCollector ?: 
                new UrlCollector(null, $this->config->getConfig('UrlCollector'));
        $this->contentDelivery = $contentDelivery ?:
                new ContentDelivery($this->config->getConfig('ContentDelivery'));
        $this->parser = $parser ?: new Parser();
        $this->dataStore = $dataStore ?: new DataStore($this->config->getConfig('DataStore'));
    }

    function setUrlCollector($urlCollector) {
        $this->urlCollector = $urlCollector;
        return $this;
    }

    
    public function run($startUrl = '') {
        if (!$startUrl) {
            $startUrl = $this->config->getConfig('General.StartUrl');

            if (!$startUrl) {
                throw new Exception('No initial url');
            }
        }

        $this->urlCollector->setStartUrl($startUrl);

        // TODO: from config
        $limit = 10;
        while ($limit > 0 && ($url = $this->urlCollector->next($this->urlMatcherCallback))) {
            $limit--;

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
        $this->urlMatcherCallback = $callback;
        return $this;
    }

}