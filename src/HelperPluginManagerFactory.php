<?php

namespace Zend\Expressive\Phptal;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;

/**
 * Create and return HelperPluginManager instance.
 * Zend\View and Zend\I18n are required
 */
class HelperPluginManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $manager = new HelperPluginManager($container);

        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['view_helpers']) ? $config['view_helpers'] : [];
        (new Config($config))->configureServiceManager($manager);
        $manager->setRenderer((new PhpRenderer)->setHelperPluginManager($manager));
        return $manager;
    }
}
