<?php

namespace Zend\Expressive\Phptal\Helper;

use Zend\Expressive\Helper\UrlHelper as BaseHelper;
use Zend\Expressive\Phptal\Helper\HelperInterface;

class UrlHelper implements HelperInterface
{
    const HELPER_NAME = 'url';
    
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
     * Proxies to `Zend\Expressive\Helper\UrlHelper::generate()`
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function __invoke($route = null, $params = [])
    {
        return $this->helper->generate($route, $params);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHelperName()
    {
        return self::HELPER_NAME;
    }
}
