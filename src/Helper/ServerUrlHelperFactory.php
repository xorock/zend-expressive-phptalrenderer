<?php

namespace Zend\Expressive\Phptal\Helper;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\ServerUrlHelper as BaseHelper;
use Zend\Expressive\Phptal\Exception\MissingHelperException;

/**
 * Factory for creating a Helper instance.
 */
class ServerUrlHelperFactory
{
    /**
     * @param ContainerInterface $container
     * @return ServerUrlHelper
     * @throws MissingHelperException if ServerUrlHelper service is missing.
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(BaseHelper::class)) {
            throw new MissingHelperException(sprintf(
                '%s requires that the %s service be present; not found',
                UrlHelper::class,
                BaseHelper::class
            ));
        }

        return new ServerUrlHelper($container->get(BaseHelper::class));
    }
}