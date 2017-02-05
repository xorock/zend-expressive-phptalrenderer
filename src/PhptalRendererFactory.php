<?php

namespace Zend\Expressive\Phptal;

use Interop\Container\ContainerInterface;
use PHPTAL as PhptalEngine;

/**
 * Create and return a PHPTAL template instance.
 *
 * Optionally uses the service 'config', which should return an array. This
 * factory consumes the following structure:
 *
 * <code>
 * 'templates' => [
 *     'extension' => 'file extension used by templates; defaults to html',
 * ],
 * </code>
 *
 * If the service PHPTAL exists, that value will be used
 * for the PhptalEngine; otherwise, this factory invokes the PhptalEngineFactory
 * to create an instance.
 */
class PhptalRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return PhptalRenderer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['templates']) ? $config['templates'] : [];
        
        // Set file extension
        $extension = isset($config['extension']) ? $config['extension'] : 'html';
        
        // Create the engine instance:
        $engine = $this->createEngine($container);
        
        // Inject environment
        $phptalRenderer = new PhptalRenderer($engine, $extension);
        
        return $phptalRenderer;
    }
    
    /**
     * Create and return a PHPTAL Engine instance.
     *
     * If the container has the PHPTAL engine service, returns it.
     *
     * Otherwise, invokes the PhptalEngineFactory with the $container to create
     * and return the instance.
     *
     * @param ContainerInterface $container
     * @return PhptalEngine
     */
    private function createEngine(ContainerInterface $container)
    {
        if ($container->has(PhptalEngine::class)) {
            return $container->get(PhptalEngine::class);
        }
        
        $engineFactory = new PhptalEngineFactory();
        return $engineFactory($container);
    }
}
