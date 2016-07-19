<?php

namespace Zend\Expressive\Phptal;

use ArrayObject;
use DirectoryIterator;
use Interop\Container\ContainerInterface;
use PHPTAL as PhptalEngine;
use PHPTAL_PreFilter_Compress;
use PHPTAL_PreFilter_StripComments;
use PHPTAL_TalesRegistry;
use Zend\Expressive\Phptal\Tales\Helper as TalesHelper;
use Zend\Expressive\Phptal\HelperManager;
use Zend\Expressive\Phptal\Helper;

/**
 * Create and return a PHPTAL engine instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * // if enabled, forces to reparse templates every time
 * 'debug' => boolean,
 * 'templates' => [
 *     'paths' => [
 *         // Paths may be strings or arrays of string paths.
 *     ],
 * ],
 * 'phptal' => [
 *     'cache_dir' => 'path to cached templates',
 *     // if enabled, delete all template cache files before processing
 *     'cache_purge_mode' => boolean,
 *     // set how long compiled templates and phptal:cache files are kept; in days 
 *     'cache_lifetime' => 30,
 *     'encoding' => 'set input and ouput encoding; defaults to UTF-8',
 *     // one of the predefined constant: PHPTAL::HTML5,  PHPTAL::XML, PHPTAL::XHTML
 *     'output_mode' => PHPTAL::HTML5,
 *     // set whitespace compression mode
 *     'compress_whitespace' => boolean,
 *     // strip all html comments
 *     'strip_comments' => boolean,
 * ],
 * </code>
 * 
 * By default, this factory attaches the Helper\UrlHelper and Helper\ServerUrlHelper
 * to the engine and registers helper: extension modifier.
 */
class PhptalEngineFactory
{
    /**
     * @param ContainerInterface $container
     * @return PhptalEngine
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        
        if (! is_array($config) && ! $config instanceof ArrayObject) {
            throw new Exception\InvalidConfigException(sprintf(
                '"config" service must be an array or ArrayObject for the %s to be able to consume it; received %s',
                __CLASS__,
                (is_object($config) ? get_class($config) : gettype($config))
            ));
        }
        
        // Set debug mode
        $debug = array_key_exists('debug', $config) ? (bool) $config['debug'] : false;
        $config = $this->mergeConfig($config);
        
        // Create the engine instance:
        $engine = new PhptalEngine();
        
        // Change the compiled code destination if set in the config
        if (isset($config['cache_dir'])) {
            $engine->setPhpCodeDestination($config['cache_dir']);
        }
        
        // Configure the encoding
        if (isset($config['encoding'])) {
            $engine->setEncoding($config['encoding']);
        }
        
        // Configure the output mode
        $outputMode = isset($config['output_mode']) ? $config['output_mode'] : PHPTAL::HTML5;
        $engine->setOutputMode($outputMode);
        
        // Set template repositories
        if (isset($config['paths'])) {
            $engine->setTemplateRepository($config['paths']);
        }
        
        // Configure cache lifetime
        if (isset($config['cache_lifetime'])) {
            $engine->setCacheLifetime($config['cache_lifetime']);
        }
        
        // If purging of the tal template cache is enabled
        // find all template cache files and delete them
        $cachePurgeMode = isset($config['cache_purge_mode']) ? (bool) $config['cache_purge_mode'] : false;
        
        if ($cachePurgeMode) {
            $cacheFolder = $engine->getPhpCodeDestination();
            if (is_dir($cacheFolder)) {
                foreach (new DirectoryIterator($cacheFolder) as $cacheItem) {
                    if (strncmp($cacheItem->getFilename(), 'tpl_', 4) != 0 || $cacheItem->isdir()) {
                        continue;
                    }
                    @unlink($cacheItem->getPathname());
                }
            }
        }

        // Configure the whitespace compression mode
        $compressWhitespace = isset($config['compress_whitespace']) 
            ? (bool) $config['compress_whitespace'] 
            : false;
        
        if ($compressWhitespace) {
            $engine->addPreFilter(new PHPTAL_PreFilter_Compress());
        }
        
        // Strip html comments and compress un-needed whitespace
        $stripComments = isset($config['strip_comments']) 
            ? (bool) $config['strip_comments'] 
            : false;
        
        if ($stripComments) {
            $engine->addPreFilter(new PHPTAL_PreFilter_StripComments());
        }
        
        if ($debug) {
            $engine->setForceReparse(true);
        }
        
        $this->injectHelpers($engine, $container);
        
        return $engine;
    }
    
    /**
     * Inject helpers into the PHPTAL instance.
     * 
     * @param PHPTAL $template
     * @param ContainerInterface $container
     * @throws Exception\MissingHelperException
     */
    private function injectHelpers(PhptalEngine $template, ContainerInterface $container)
    {        
        if (!$container->has(HelperManager::class)) {
            throw new Exception\MissingHelperException(sprintf(
                'An instance of %s is required in order to register new helper',
                HelperManager::class
            ));
        }
        
        $helperManager = $container->get(HelperManager::class);
        
        $template->set('helper', $helperManager);
        
        if ($container->has(Helper\UrlHelper::class)) {
            $helperManager->registerHelper($container->get(Helper\UrlHelper::class));
        }
        
        if ($container->has(Helper\ServerUrlHelper::class)) {
            $helperManager->registerHelper($container->get(Helper\ServerUrlHelper::class));
        }

        $talesRegistry = PHPTAL_TalesRegistry::getInstance();
        if (! $talesRegistry->isRegistered('helper')) {
            $talesRegistry->registerPrefix('helper', [TalesHelper::class, 'helper']);
        }
    }
    
    /**
     * Merge expressive templating config with PHPTAL config.
     *
     * Pulls the `templates` and `phptal` top-level keys from the configuration,
     * if present, and then returns the merged result, with those from the phptal
     * array having precedence.
     *
     * @param array|ArrayObject $config
     * @return array
     * @throws Exception\InvalidConfigException if a non-array, non-ArrayObject
     *     $config is received.
     */
    private function mergeConfig($config)
    {
        $config = $config instanceof ArrayObject ? $config->getArrayCopy() : $config;
        if (! is_array($config)) {
            throw new Exception\InvalidConfigException(sprintf(
                'config service MUST be an array or ArrayObject; received %s',
                is_object($config) ? get_class($config) : gettype($config)
            ));
        }
        $expressiveConfig = (isset($config['templates']) && is_array($config['templates']))
            ? $config['templates']
            : [];
        $phptalConfig = (isset($config['phptal']) && is_array($config['phptal']))
            ? $config['phptal']
            : [];
        return array_replace_recursive($expressiveConfig, $phptalConfig);
    }
}
