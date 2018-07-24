<?php

namespace Yugeon\Uniparser;

class Uniparser {
    private $config;
    private $urlCollector;
    private $contentDelivery;
    private $parser;
    private $dataStore;

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
        $this->urlCollector = $urlCollector ?: new UrlCollector();
        $this->contentDelivery = $contentDelivery ?:
                new ContentDelivery($this->config->getConfig('ContentDelivery'));
        $this->parser = $parser ?: new Parser();
        $this->dataStore = $dataStore ?: new DataStore($this->config->getConfig('DataStore'));
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
        while ($limit > 0 && ($url = $this->urlCollector->next())) {
            $limit--;

            try {
                echo $url . "\n";
                $content = $this->contentDelivery->getContent($url);
                $this->parser->setContent($content);
                $urls = $this->parser->getAllUrls();
                $this->urlCollector->add($urls);

                if ($this->parser->isExist('div#product_infos')) {
                    $data = [
                        'id' => $this->parser->filterXPath('#valueSize'),
                        'name' => $this->parser->filterText('#product_infos > div.productHeader > h1 > span'),
                        'price' => $this->parser->filterText('#price')
                    ];

                    $this->dataStore->save($url, $data);
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }

    }
}