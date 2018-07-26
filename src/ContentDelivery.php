<?php

namespace Yugeon\Uniparser;
use \Curl\Curl;

/**
 * Description of ContentDelivery
 *
 * @author yugeon
 */
class ContentDelivery {

    private $curl;
    private $config;
    private $allowedContentTypes;

    function __construct($config, $curl = null) {
        if (!$curl) {
            $curl = new Curl();
        }

        $this->curl = $curl;

        if ($config) {
            $this->setConfig($config);
        }
    }

    public function init() {
        if (!$this->config) {
            return;
        }

        $this->tuneCurl();

        $allowedContentTypes = $this->config->getConfig('AllowedContentTypes', 'text/html');
        $this->setContentTypes($allowedContentTypes);
    }

    private function tuneCurl() {
        $config = $this->getConfig()->getConfig('CurlOptions');

        $this->curl->setOpt(CURLOPT_USERAGENT, $config['UserAgent']);
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, $config['ReturnTransfer']);
        $this->curl->setOpt(CURLOPT_FAILONERROR, true);
        $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, $config['FoollowLocation']);
        $this->curl->setOpt(CURLOPT_MAXREDIRS, $config['MaxRedirs']);
        $this->curl->setOpt(CURLOPT_PROTOCOLS, CURLPROTO_HTTP|CURLPROTO_HTTPS);
        $this->curl->setOpt(CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP|CURLPROTO_HTTPS);
        $this->curl->setOpt(CURLOPT_CONNECTTIMEOUT, $config['ConnectTimeout']);
        $this->curl->setOpt(CURLOPT_TIMEOUT, $config['Timeout']);

        // Proxy
        $config = $this->getConfig()->getConfig('Proxy');
        if ($config['UseProxy']) {
            $this->curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
            $this->curl->setOpt(CURLOPT_PROXY, $config['Proxy']);
            $this->curl->setOpt(CURLOPT_PROXYPORT, $config['ProxyPort']);
            $this->curl->setOpt(CURLOPT_PROXYUSERPWD, $config['ProxyUserPwd']);
        }        
    }

    /**
     *
     * @return Config
     * @throws \Exception
     */
    function getConfig() {
        if (!$this->config) {
            throw new \Exception('Config for ContentDelivery object not found');
        }
        return $this->config;
    }

    function setConfig($config) {
        if (!($config instanceof Config)) {
            if (is_array($config)) {
                $configObj = new Config();
                $config = $configObj->setConfig($config);
            } else {
                throw new \Exception('Config not instance of Yugeon\Uniparser\Config');
            }
        }
        $this->config = $config;

        $this->init();

        return $this;
    }

    public function getContent($url) {

        $this->curl->get($url);
        if ($this->curl->error) {
            throw new \Exception('Error: ' . $this->curl->errorCode . ': ' . $this->curl->errorMessage . "\n");
        }

        if (!$this->checkContentType()) {
            throw new \Exception('Not allowed content type');
        }

        return $this->curl->response;
        
    }

    public function setContentTypes($contentTypes) {
        $this->allowedContentTypes = is_array($contentTypes) ? $contentTypes : [$contentTypes];
        return $this;
    }

    public function checkContentType() {
        if ($this->allowedContentTypes) {
            if ($this->curl->responseHeaders['Content-Type']) {
                $isFind = false;
                foreach ($this->allowedContentTypes as $contentType) {
                    if (false !== strpos($this->curl->responseHeaders['Content-Type'], $contentType)) {
                        $isFind = true;
                        break;
                    }
                }

                return $isFind;
            }

            return false;
        }

        return true;
    }

}
