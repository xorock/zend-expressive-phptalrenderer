<?php

namespace Zend\Expressive\Phptal\Helper;

use Zend\Expressive\Helper\ServerUrlHelper as BaseHelper;
use Zend\Expressive\Phptal\Helper\HelperInterface;

class ServerUrlHelper implements HelperInterface
{
    const HELPER_NAME = 'serverurl';
    
    /**
     * @var BaseHelper
     */
    private $helper;
    
    /**
     * @param BaseHelper $helper
     */
    public function __construct(BaseHelper $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * Return a path relative to the current request URI.
     *
     * Proxies to `Zend\Expressive\Helper\ServerUrlHelper::generate()`.
     *
     * @param null|string $path
     * @return string
     */
    public function __invoke($path = null)
    {
        return $this->helper->generate($path);
    }
    
    /**
     * Proxies to `Zend\Expressive\Helper\ServerUrlHelper::setUri()`
     * @param UriInterface $uri
     */
    public function setUri(UriInterface $uri)
    {
        $this->helper->setUri($uri);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHelperName()
    {
        return self::HELPER_NAME;
    }
}
