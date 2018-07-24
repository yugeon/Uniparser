<?php

namespace Yugeon\Uniparser;

use \Symfony\Component\Config\Definition\ConfigurationInterface;
use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Description of Config
 *
 * @author yugeon
 */
class Config {

    private $configPath;
    private $config;
    private $isValid = false;

    function __construct($configPath = null) {
        if ($configPath) {
            $this->configPath = $configPath;
        }

        if ($this->configPath) {
            $this->load();
        }
    }

    function getConfigPath() {
        return $this->configPath;
    }

    function setConfigPath($configPath) {
        $this->configPath = $configPath;
        return $this;
    }

    public function isValid() {
        return $this->isValid;
    }

    public function load($configPath = null)
    {
        $this->isValid = false;

        if ($configPath) {
            $this->setConfigPath($configPath);
        }

        $configStr = @\file_get_contents($this->configPath);
        if (false === $configStr) {
            throw new \Exception("Can't read the file {$this->configPath}");
        }

        try {
            $this->config = Yaml::parse($configStr);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        $this->isValid = true;
        return $this->config;
    }

    public function getConfig($section = '', $default = '') {

        if ($section) {
            $keys = explode('.', $section);

            if (isset($this->config[$keys[0]])) {
                $configValue = $this->config[$keys[0]];
                array_shift($keys);
            } else {
                return $default;
            }

            foreach ($keys as $key) {
                if (!is_array($configValue) || !isset($configValue[$key])) {
                    return $default;
                }

                $configValue = $configValue[$key];
            }

            return $configValue;
        }

        return $this->config;
    }

    function setConfig($config) {
        $this->config = $config;
        return $this;
    }

//    public function supports($resource, $type = null)
//    {
//        return is_string($resource) && 'yml' === pathinfo(
//            $resource,
//            PATHINFO_EXTENSION
//        );
//    }

//    public function getConfigTreeBuilder()
//    {
//        $treeBuilder = new TreeBuilder();
//        $rootNode = $treeBuilder->root('blog');
//
//        $rootNode
//            ->children()
//                ->scalarNode('title')
//                    ->isRequired()
//                ->end()
//                ->scalarNode('description')
//                    ->defaultValue('')
//                ->end()
//                ->booleanNode('rss')
//                    ->defaultValue(false)
//                ->end()
//                ->integerNode('posts_main_page')
//                    ->min(1)
//                    ->max(10)
//                    ->defaultValue(5)
//                ->end()
//                ->arrayNode('social')
//                    ->prototype('array')
//                        ->children()
//                            ->scalarNode('url')->end()
//                            ->scalarNode('icon')->end()
//                        ->end()
//                    ->end()
//                ->end()
//            ->end()
//        ;
//
//        return $treeBuilder;
//    }

}
