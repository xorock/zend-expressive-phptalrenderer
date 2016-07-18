<?php

namespace Zend\Expressive\Phptal\Helper;

use Interop\Container\ContainerInterface;
use Zend\Expressive\Helper\UrlHelper as BaseHelper;
use Zend\Expressive\Phptal\Exception\MissingHelperException;

/**
 * Factory for creating a Helper instance.
 */
class UrlHelperFactory
{
    /**
     * @param ContainerInterface $container
     * @return UrlHelper
     * @throws MissingHelperException if UrlHelper service is missing.
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

        return new UrlHelper($container->get(BaseHelper::class));
    }
}